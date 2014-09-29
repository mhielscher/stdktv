<?php

if (isset($_GET) && isset($_GET['answer']))
    //$answer = filter_var($_GET['answer'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE);
    $answer = $_GET['answer'];
else
    $answer = "";
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
        
        <link rel="stylesheet" href="css/normalize.min.css" type="text/css" />
        <!--<link rel="stylesheet" href="css/main.css" type="text/css" />-->
        
	    <script src="js/vendor/modernizr-latest.js"></script>
        <!-- <script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', 'UA-35525132-1', 'auto');
			ga('send', 'pageview');

		</script> -->
    </head>
    <body>
        <h1>Weekly Standardized Kt/V</h1>
        <form id="stdktv-form" action="/calculate.php" method="GET">
        <table>
            <tr>
                <td>Treatment time:</td>
                <td><input type="text" name="time" required /></td>
            </tr>
            <tr>
                <td>Treatments per week:</td>
                <td><input type="text" name="days" required /></td>
                <td><em>decimals okay</em></td>
            </tr>
            <tr>
                <td></td>
                <td>Pre-dialysis</td>
                <td>Post-dialysis</td>
            </tr>
            <tr>
                <td>Blood Urea Nitrogen:</td>
                <td><input type="text" name="prebun" required /></td>
                <td><input type="text" name="postbun" required /></td>
            </tr>
            <tr>
                <td>Total UF:</td>
                <td><input type="text" name="uf" required /></td>
            </tr>
            <tr>
                <td><input id="submit-button" type="submit" value="Calculate" /></td>
            </tr>
        </table>
        </form>
        <h2>Std Kt/V = <span class="answer"><?php echo $answer; ?></span></h2>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.8.2.js"><\/script>')</script>

        <script type="text/javascript">
            $("#stdktv-form").submit(function (ev) {
                ev.preventDefault();
                var $form = $(this),
                    time = $form.find( "input[name='time']" ).val(),
                    days = $form.find( "input[name='days']" ).val(),
                    prebun = $form.find( "input[name='prebun']" ).val(),
                    postbun = $form.find( "input[name='postbun']" ).val(),
                    uf = $form.find( "input[name='uf']" ).val(),
                    url = $form.attr("action");

                $.post(
                    url,
                    {
                        time: time,
                        days: days,
                        prebun: prebun,
                        postbun: postbun,
                        uf: uf
                    },
                    function (data) {
                        $("span.answer").text(data.answer);
                        console.log("Added answer: ");
                        console.log(data);
                    }
                );
                console.log("Did submit.");
            });
        </script>
    </body>
</html>