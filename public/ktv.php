<?php

if (isset($_GET) && isset($_GET['avg_sp']))
{
    $spKtV = filter_var($_GET['avg_sp'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
    $stdKtV = filter_var($_GET['std'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
}
else
{
    $spKtV = 0.0;
    $stdKtV = 0.0;
}

?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>StdKt/V Calculator for Hemodialysis - Weekly Standardized Kt/V</title>
        <!--<meta name="description" content="">-->
        <meta name="HandheldFriendly" content="True">
        <meta name="viewport" content="width=device-width">
        
        <link rel="canonical" href="http://stdktv.com/">

        <link rel="stylesheet" src="//normalize-css.googlecode.com/svn/trunk/normalize.css">
        <!-- <link rel="stylesheet" href="css/vendor/normalize.min.css" type="text/css"> -->
        <link rel="stylesheet" href="/css/main.css" type="text/css">
        
	    <script src="/js/vendor/modernizr-latest.js"></script>

        <?php include_once("../includes/analyticstracking.local.php"); ?>
    </head>
    <body>
        <div id="main-header">
            <h1>Standard Kt/V Calculator</h1>
        </div>
        <div id="top-content">
            <p><abbr title="K is clearance/effectiveness of the dialyzer, t is time on the dialysis
            machine, and V is the patient's total body water.">Kt/V</abbr> (pronounced <em>kay tee
            over vee</em>) is a measure of the effectiveness of dialysis independent of body type.
            <strong>Single pool Kt/V</strong> (<abbr>spKt/V</abbr>) measures the effectiveness of a
            single treatment.</p>

            <p><strong>Standardized Kt/V</strong> (<abbr>stdKt/V</abbr>) estimates the weekly
            effectiveness of a dialysis regimen.</p>
        </div>
        <div id="calculator">
            <form id="stdktv-form" action="/calculate.php" method="GET">
            <table id="stdktv-table">
                <tr class="table-header">
                    <td colspan="4">
                        Weekly Standardized Kt/V
                    </td>
                </tr>
                <tr>
                    <td class="label-cell"><label for="time">Treatment time:</label></td>
                    <td class="input-cell"><input type="text" name="time" size="5" pattern="[0-9:.]+"
                    required autofocus title="hh:mm, hours, or minutes"></td>

                    <td class="label-cell"><label for="schedules">Treatment schedule:</label></td>
                    <td id="standard-schedule">
                        <input type="hidden" name="days" value="3">
                        <select id="schedules-select" name="schedules">
                            <option value="7">Every day</option>
                            <option value="6">6 days/week</option>
                            <option value="5">5 days/week</option>
                            <option value="4">4 days/week</option>
                            <option value="3" selected>3 days/week</option>
                            <option value="5.25">3 on / 1 off</option>
                            <option value="4.6667">2 on / 1 off</option>
                            <option value="3.5">Every other day</option>
                            <option value="other">Other (advanced)</option>
                        </select>
                    </td>
                </tr>
                <tr id="other-schedule">
                    <td colspan="1"></td>
                    <td colspan="3">
                        <input type="text" name="schedule_treatments" value="" size="2" disabled>
                        treatments every
                        <input type="text" name="schedule_duration" value="" size="2" disabled>
                        days.
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>Pre-dialysis</td>
                    <td>Post-dialysis</td>
                </tr>
                <tr>
                    <td class="label-cell"><label for="prebun">BUN</label></td>
                    <td class="input-cell"><input type="text" name="prebun" size="5" required></td>
                    <td class="input-cell"><input type="text" name="postbun" size="5" required></td>
                    <td id="bun-units"><span>mg/dL</span><input type="hidden" name="bun-units" value="mg/dL"></td>
                </tr>
                <tr>
                    <td class="label-cell"><label for="uf">Total UF</label></td>
                    <td class="input-cell"><input type="text" name="uf" size="5" required></td>
                </tr>
                <tr>
                    <td class="label-cell"><label for="postweight">Post Weight</label></td>
                    <td class="input-cell"><input type="text" name="postweight" size="5" required></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <a href="#advanced">Advanced</a>
                    </td>
                </tr>
                <tr class="advanced urea-volume-calc">
                    <td colspan="3">
                        <em>Fill in all data you have available; leave the rest blank.</em>
                    </td>
                </tr>
                <tr class="advanced urea-volume-calc">
                    <td class="label-cell"><label for="age">Age</label></td>
                    <td class="input-cell"><input type="text" name="age" size="5" disabled></td>
                    <td class="label-cell"><label for="height">Height</label></td>
                    <td class="input-cell"><input type="text" name="height" size="5" disabled></td>
                </tr>
                <tr class="advanced urea-volume-calc">
                    <td>
                        Male <input type="radio" name="sex" value="male" disabled>
                    </td>
                    <td colspan="2">
                        Female <input type="radio" name="sex" value="female" disabled>
                    </td>
                    <td>
                        <a href="#removesex">Unselect</a>
                    </td>
                </tr>
                <tr class="advanced urea-volume-calc">
                    <td colspan="2">
                        African American
                        <input type="checkbox" name="african_american" value="true" disabled>
                    </td>
                </tr>
                <tr class="advanced urea-volume-calc">
                    <td colspan="2">
                        Diabetes
                        <input type="checkbox" name="diabetes" value="true" disabled>
                    </td>
                </tr>
                <tr id="manual-urea-vol-toggle" style="display: none;">
                    <td colspan="2">
                        <a href="#manualureavolume">Enter urea distribution volume manually</a>
                    </tr>
                </tr>
                <tr class="urea-volume-manual" style="display: none;">
                    <td>Urea Distribution Volume:</td>
                    <td class="input-cell"><input type="text" name="ureavolume" size="5" disabled></td>
                </tr>
                <tr>
                    <td class="input-cell"><input id="submit-button" type="submit" value="Calculate"></td>
                </tr>
                <tr>
                    <td colspan="3">sp Kt/V = <span class="spktv"><?php if (isset($_GET['avg_sp'])) echo round($spKtV, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3">std Kt/V = <span class="stdktv"><?php if (isset($_GET['std'])) echo round($stdKtV, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3"><a style="font-size: 73%;" href="#">Expand</a></td>
                    <!-- onclick: show several other rows of calculations:
                            eKt/V
                            estimated urea volume
                            etc.
                    -->
                </tr>
            </table>
            </form>
        </div>
        <div id="instructions-panel">
            <div id="instructions-time" class="instructions">
                <p>hh:mm (3:30), minutes (210) or decimal hours (3.5)</p>
            </div>
            <div id="instructions-days" class="instructions">
                <p>Average number of treatments per week</p>
            </div>
            <!-- ... -->
            <div id="instructions-sex" class="instructions">
                <p></p>
                <p style="font-size: 73%">Note: Accurate urea volume estimates depend on physiological
                and hormonal sex due to body development and anatomical differences. If this is more
                complicated than black-and-white, use your best judgement to manually split the
                difference in body water estimates.</p>
        </div>
        <!--<div id="ads-right">
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!- KtV Side Text ->
            <ins class="adsbygoogle"
                 style="display:inline-block;width:300px;height:600px"
                 data-ad-client="ca-pub-4135528310907265"
                 data-ad-slot="6542006405"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>-->
        <div id="ads-footer">
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!-- KtV Footer Ad -->
            <ins class="adsbygoogle"
                 style="display:inline-block;width:728px;height:90px"
                 data-ad-client="ca-pub-4135528310907265"
                 data-ad-slot="3448939207"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
        <script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.11.1.js"><\/script>')</script>

        <script type="text/javascript">
            function updateOtherSchedule() {
                if ($.isNumeric($(':input[name="schedule_treatments"]').val().trim()) &&
                    $.isNumeric($(':input[name="schedule_duration"]').val().trim())) {
                    var daysOn = Math.round($(':input[name="schedule_treatments"]').val().trim());
                    var daysTotal = Math.round($(':input[name="schedule_duration"]').val().trim());
                    if (daysOn > daysTotal)
                        $(':input[name="days"]').val(7);
                    else
                        $(':input[name="days"]').val((daysOn/daysTotal * 7).toPrecision(3));
                }
            }

            function validate() {
                // time = valid integer (minutes), valid float < 10 (hours), or hh:mm
                // days = valid float <= 7, > 0
                // prebun = valid float
                // postbun = valid float
                // bun-units = "mg/dL" or "mmol/L"
                // uf = valid float > 0
                // postweight = valid float
                // if advanced:
                    // age = valid integer
                    // height = valid integer (cm or inches) or f'in"
                    // sex = NULL or "male" or "female"
                    // african_american = NULL or "true"
                    // diabetes = NULL or "true"
            }

            function calculate() {

            }

            $("#stdktv-form").submit(function (ev) {
                ev.preventDefault();
                var url = $('form').attr("action");

                // Re-enable `days` if #other-schedule has disabled it
                $('input[name="days"]').prop("disabled", false);

                var formData = {};
                // Grab all enabled inputs except radio buttons and checkboxes
                $(':input:not(input[type="radio"], :input[type="checkbox"])').each(function(i,e) {
                    if ($(e).val() !== "" && !$(e).attr("disabled"))
                        formData[$(e).attr("name")] = $(e).val();
                });
                // Handle radio buttons and checkboxes
                $(':input:checked').each(function(i,e) {
                    if (!$(e).attr("disabled")) {
                        formData[$(e).attr("name")] = $(e).val();
                        console.log("Checked "+$(e).attr("name"))
                    }
                });
                console.log(formData);

                $.post(
                    url,
                    formData,
                    function (data) {
                        $("span.spktv").text(data.avg_sp.toPrecision(3));
                        $("span.stdktv").text(data.std.toPrecision(3));
                        console.log("Added answer: ");
                        console.log(data);
                    }
                );
                console.log("Did submit.");

                if ($('#other-schedule').is(':visible'))
                    $('input[name="days"]').prop("disabled", true);
            });
            
            $("#schedules-select").change(function (ev) {
                if ($(this).val() === "other") {
                    $(':input[name="days"]').prop("disabled", true);
                    $("#other-schedule").show();
                    $("#other-schedule :input").prop("disabled", false);
                    updateOtherSchedule();
                }
                else {
                    $(':input[name="days"]').val($(this).val());
                    $("#other-schedule").hide();
                    $("#other-schedule :input").prop("disabled", true);
                    $(':input[name="days"]').prop("disabled", false);
                }
            });

            $('a[href="#advanced"]').click(function (ev) {
                ev.preventDefault();
                // Toggle the 'disabled' property on all advanced options
                $(".advanced :input").prop("disabled", function (i,val) {
                    return !val;
                });
                $(".advanced").toggle();
            });

            $('a[href="#removesex"]').click(function (ev) {
                ev.preventDefault();
                $(':input[name="sex"]').prop("checked", false);
            });

            $('a[href="#manualureavolume"]').click(function (ev) {
                ev.preventDefault();
                $(".urea-volume-calc :input").prop("disabled", function (i,val) {
                    return !val;
                });
                $(".urea-volume-calc").toggle();
                $(".urea-volume-manual :input").prop("disabled", function (i,val) {
                    return !val;
                });
                $(".urea-volume-manual").toggle();
                $("#manual-urea-vol-toggle a").text("Estimate urea distribution volume anthropometrically");
            });

            // Heuristic to determine units
            // Can be manually changed by clicking the unit display
            $('input[name="prebun"], input[name="postbun"]').change(function (ev) {
                var prebun = $('input[name="prebun"]').val();
                var postbun = $('input[name="postbun"]').val();
                if (prebun > 32 || postbun > 26) {
                    $('#bun-units span').text("mg/dL");
                    $('#bun-units input').val("mg/dL");
                }
                else if (prebun < 27 || postbun < 12) {
                    $('#bun-units span').text("mmol/L");
                    $('#bun-units input').val("mmol/L");
                }
            });

            $('#bun-units').click(function (ev) {
                if ($('#bun-units input').val() === "mg/dL") {
                    $('#bun-units span').text("mmol/L");
                    $('#bun-units input').val("mmol/L");
                }
                else {
                    $('#bun-units span').text("mg/dL");
                    $('#bun-units input').val("mg/dL");
                }
            });

            $(':input').focus(function (ev) {
                $('div.instructions').hide();
                $('div#instructions-'+$(this).attr('name')).show();
            });

            $(':input[name="schedule_treatments"], :input[name="schedule_duration"]').change(updateOtherSchedule);
        </script>
    </body>
</html>
