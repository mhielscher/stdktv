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
        <title>Std Kt/V Calculator for Hemodialysis</title>
        <!--<meta name="description" content="">-->
        <meta name="HandheldFriendly" content="True" />
        <meta name="viewport" content="width=device-width" />
        
        <link rel="stylesheet" src="//normalize-css.googlecode.com/svn/trunk/normalize.css" />
        <!-- <link rel="stylesheet" href="css/vendor/normalize.min.css" type="text/css" /> -->
        <link rel="stylesheet" href="/css/main.css" type="text/css" />
        
	    <script src="/js/vendor/modernizr-latest.js"></script>
    </head>
    <body>
        <div class="top-content">
            <p>Kt/V is a measure of the effectiveness of dialysis. Single pool Kt/V (spKt/V) measures the effectiveness of a single treatment. Standardized Kt/V (stdKt/V) estimates the weekly effectiveness of a dialysis regimen.</p>
        </div>
        <div class="calculator">
            <form id="stdktv-form" action="/calculate.php" method="GET">
            <table id="stdktv-table">
                <tr class="table-header">
                    <td colspan="3">
                        Weekly Standardized Kt/V
                    </td>
                </tr>
                <tr>
                    <td>Treatment time:</td>
                    <td class="input-cell"><input type="text" name="time" size="7" required /></td>
                </tr>
                <tr>
                    <td>Treatments per week:</td>
                    <td class="input-cell"><input type="text" id="days-input" name="days" size="7" required /></td>
                    <td>
                        <select id="schedules-select" name="schedules">
                            <option value=""><em>Non-weekly schedules:</em></option>
                            <option value="4.6667">2 on / 1 off</option>
                            <option value="3.5">Every other day</option>
                        </select>
                        <!--
                        insert dropdown with common schedules
                        3 days/week
                        every other day
                        4 days/week
                        2 on / 1 off
                        5 days/week
                        3 on / 1 off
                        6 days / week
                        every day
                        other
                            - enter [treatments] per [time period]
                        -->
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>Pre-dialysis</td>
                    <td>Post-dialysis</td>
                </tr>
                <tr>
                    <td>Blood Urea Nitrogen:</td>
                    <td class="input-cell"><input type="text" name="prebun" size="7" required /></td>
                    <td class="input-cell"><input type="text" name="postbun" size="7" required /></td>
                </tr>
                <tr>
                    <td>Total UF:</td>
                    <td class="input-cell"><input type="text" name="uf" size="7" required /></td>
                </tr>
                <tr>
                    <td>Post Weight:</td>
                    <td class="input-cell"><input type="text" name="postweight" size="7" required /></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <a href="#advanced">Advanced</a>
                    </td>
                </tr>
                <tr class="advanced">
                    <td>Age:</td>
                    <td class="input-cell"><input type="text" name="age" size="3" /></td>
                </tr>
                <tr class="advanced">
                    <td>Height:</td>
                    <td class="input-cell"><input type="text" name="height" size="5" /></td>
                </tr>
                <tr class="advanced">
                    <td>
                        Male <input type="radio" name="sex" value="male" />
                    </td>
                    <td>
                        Female <input type="radio" name="sex" value="female" />
                    </td>
                </tr>
                <tr class="advanced">
                    <td colspan="2">
                        African American
                        <input type="checkbox" name="african_american" value="true" />
                    </td>
                </tr>
                <tr class="advanced">
                    <td colspan="2">
                        Diabetes
                        <input type="checkbox" name="diabetes" value="true" />
                    </td>
                </tr>
                <tr>
                    <td class="input-cell"><input id="submit-button" type="submit" value="Calculate" /></td>
                </tr>
                <tr>
                    <td colspan="3">sp Kt/V = <span class="spktv"><?php if (isset($_GET['avg_sp'])) echo round($spKtV, 2); ?></span></td>
                </tr>
                <tr>
                    <td colspan="3">std Kt/V = <span class="stdktv"><?php if (isset($_GET['std'])) echo round($stdKtV, 2); ?></span></td>
                </tr>
            </table>
            </form>
        </div>
        <div class="footer-ad">
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!-- KtV Leaderboard -->
            <ins class="adsbygoogle"
                 style="display:inline-block;width:728px;height:90px"
                 data-ad-client="ca-pub-4135528310907265"
                 data-ad-slot="2700562806"></ins>
            <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
        <script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.11.1.js"><\/script>')</script>

        <script type="text/javascript">
            $("#stdktv-form").submit(function (ev) {
                ev.preventDefault();
                var $form = $(this),
                    time = $form.find("input[name='time']" ).val(),
                    days = $form.find("input[name='days']" ).val(),
                    prebun = $form.find("input[name='prebun']" ).val(),
                    postbun = $form.find("input[name='postbun']" ).val(),
                    uf = $form.find("input[name='uf']" ).val(),
                    postweight = $form.find("input[name='postweight']").val(),
                    url = $form.attr("action");

                $.post(
                    url,
                    {
                        time: time,
                        days: days,
                        prebun: prebun,
                        postbun: postbun,
                        uf: uf,
                        postweight: postweight
                    },
                    function (data) {
                        $("span.spktv").text(data.avg_sp.toPrecision(3));
                        $("span.stdktv").text(data.std.toPrecision(3));
                        console.log("Added answer: ");
                        console.log(data);
                    }
                );
                console.log("Did submit.");
            });
            
            $("#schedules-select").change(function (ev) {
                if ($(this).val() !== "")
                    $("#days-input").val($(this).val());
            });

            $('a[href="#advanced"]').click(function (ev) {
                ev.preventDefault();
                $(".advanced").toggle();
            });
        </script>
    </body>
</html>
