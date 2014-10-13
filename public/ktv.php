<?php

if (isset($_GET) && isset($_GET['avg_sp']))
{
    $spKtV = filter_var($_GET['avg_sp'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
    $stdKtV = filter_var($_GET['std'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
}
else
{
    $spKtV = "";
    $stdKtV = "";
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
            <p>This Kt/V calculator is used to estimate maintenance hemodialysis effectiveness. It uses multiple formulas depending on how much data you enter.</p>
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
                    <td class="input-cell"><input id="submit-button" type="submit" value="Calculate" /></td>
                </tr>
                <tr>
                    <td>sp Kt/V = <span class="spktv"><?php echo round($spKtV, 2); ?></span></td>
                    <td>std Kt/V = <span class="stdktv"><?php echo round($stdKtV, 2); ?></span></td>
                </tr>
            </table>
            </form>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
        <script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.11.1.js"><\/script>')</script>

        <script type="text/javascript">

            // Original JavaScript code by Chirp Internet: www.chirp.com.au
            // Please acknowledge use of this code by including this header.

            function getCookie(name)
            {
                var re = new RegExp(name + "=([^;]+)");
                var value = re.exec(document.cookie);
                return (value != null) ? unescape(value[1]) : null;
            }

        </script>
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
        </script>
    </body>
</html>
