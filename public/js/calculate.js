/*
Inputs:
treatment time (could be inferred from other parameters?)
treatments per week OR schedule
pre bun
post bun
bun units (heuristicly guessed, manually changeable)
total uf OR (pre weight AND intra PO/IV)
post weight
weight units (heuristicly guessed, manually changeable)
(age
 height
 height units (heuristicly guessed, manually changeable)
 sex
 african american
 diabetes)
    OR
 urea volume (L)
access type (AVF/graft or venous catheter)
residual kidney function (Kru or urine collection data)

dialyzer type OR dialyzer K0A
blood flow rate
dialysate flow rate OR (flow fraction
                        UF rate) (maybe should be calculated?)
        ... OR (dialysate volune & ...?)

*/

/*
presets for dialysis types and machines
*/

$(document).ready(function () {
    $('form#stdktv-form').validate({
        rules: {
            schedule_treatments: {
                number: true,
                required: {
                    depends: function(element) {
                        return $('#schedules-select').val() == "other";
                    }
                }
            },
            schedule_duration: {
                number: true,
                required: {
                    depends: function(element) {
                        return $('#schedules-select').val() == "other";
                    }
                }
            },
            days: {
                number: true
            },
            prebun: {
                required: true,
                number: true,
                min: 0,
                max: 5000 // kind of arbitrary, based on LD50 of urea
            },
            postbun: {
                required: true,
                number: true,
                min: 0,
                max: 5000
            },
            uf: {
                required: true,
                number: true,
                min: 0
            },
            postweight: {
                required: true,
                number: true,
                min: 0
            },
            age: {
                number: true,
                min: 14,
                max: 150
            },
            height: {
                pattern: /(\d'\d\d?\")|(\d+)/
            },
            ureavolume: {
                number: true
            }
        }
    })
});

function parseForm(form) {
    var serializedForm = {};
    var formArray = form.serializeArray();
    for (var i=0; i<formArray.length; i++) {
        var name = formArray[i].name, value = formArray[i].value;
        if ('number' in $(':input[name="'+name+'"]').rules())
            serializedForm[name] = parseFloat(value);
        else if ($(':input[name="'+name+'"]').attr("type") == "checkbox" && value === "true")
            serializedForm[name] = true;
        else
            serializedForm[name] = value;
    }
    return serializedForm;
}

// Convert height from f'ii", f.ff, ii, or ccc to centimeters.
function parseHeight(rawHeight) {
    var height;
    var heightRegex = /(\d)'(\d\d?)"?/;

    var match = heightRegex.exec(rawHeight);
    if (match != null) // foot-inch notation
        height = parseInt(match[1])*12 + parseInt(match[2]);
    else // assume integer
        height = parseInt(rawHeight);

    if (height < 100) // assume inches
        height *= 2.54; // convert to cm

    return height;
}

// Convert time from hh:mm, mmm, or h.hh to hours
function parseTime(rawTime) {
    var time;
    var timeRegex = /(\d\d?):(\d\d)/;

    var match = timeRegex.exec(rawTime);
    if (match != null) // hh:mm notation
        time = parseInt(match[1]) + parseInt(match[2])/60.0;
    else {
        time = parseFloat(rawTime);
        if (time > 12) // assume minutes instead of hours
            time /= 60.0;
    }
    return time;
}

function calculate(data) {
    var results = {};

    // Convert time to hours
    var time = parseTime(data['time']);

    // Calculate urea volume
    if ('urea_volume' in data) {
        // Manual entry
        results['ureaVolume'] = data['urea_volume'];
    }
    else if ('height' in data && 'sex' in data && ('age' in data || data['sex'] == "female")) {
        var height = parseHeight(data['height']);

        // Anthropometric body water calculation
        // http://www.nature.com/ki/journal/v64/n3/full/4493991a.html
        if (data['sex'] == "male") {
            var watson = 2.447 - (0.09516*data['age']) + (0.1074*height) + (0.3362*data['postweight']);
            var ureaVolume = watson * 0.824 * Math.pow(0.998, Math.max(0, data['age']-50)/10.0);
        }
        else {
            var watson = -2.097 + (0.2466*data['postweight']) + (0.1069*height);
            var ureaVolume = watson * 0.824 * Math.pow(0.985, Math.max(0, data['age']-50)/10.0) * 1.033;
        }

        if ('african_american' in data && data['african_american'] === true)
            ureaVolume *= 1.043;
        if ('diabetes' in data && data['diabetes'] === true)
            ureaVolume *= 1.033;

        results['ureaVolume'] = ureaVolume;
    }
    else {
        // Constant proportion estimation
        results['ureaVolume'] = 0.58 * data['postweight'];
    }

    // Improved equation for estimating single-pool Kt/V at higher dialysis frequencies
    // https://clinicalresearch.ccf.org/fhn/webdocs/publications/Daugirdas%20Improved%20KtV%20pred%20NDT%202012.pdf
    var R = data['postbun']/data['prebun'];
    var short_break_GFAC = 0.0174 / (Math.floor(7/data['days']) - time/24);
    var long_break_GFAC = 0.0174 / (Math.ceil(7/data['days']) - time/24);
    var avg_GFAC = 0.0174 / (7/data['days'] - time/24);

    var short_break_spKtV = -Math.log(R - short_break_GFAC*time) + (4 - 3.5*R)*0.55 * data['uf']/results['ureaVolume'];
    var long_break_spKtV = -Math.log(R - long_break_GFAC*time) + (4 - 3.5*R)*0.55 * data['uf']/results['ureaVolume'];
    var avg_spKtV = -Math.log(R - avg_GFAC*time) + (4 - 3.5*R)*0.55 * data['uf']/results['ureaVolume'];

    results['spKtV'] = avg_spKtV;

    // Calculation of Standard Kt/V (stdKt/V) with Corrections for Postdialysis Urea Rebound
    // http://onlinelibrary.wiley.com/doi/10.1046/j.1492-7535.2003.01216.x/abstract
    var eKtV = 0.927 * avg_spKtV - 0.255 * avg_spKtV/time;
    var stdKtV = ((168*(1-Math.exp(-eKtV)))/time)/(((1-Math.exp(-eKtV))/avg_spKtV)+(168/(data['days']*time))-1);
    results['eKtV'] = eKtV;
    results['stdKtV'] = stdKtV;
    console.log(results);

    return results;
}
