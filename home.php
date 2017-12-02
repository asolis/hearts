<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
if (empty($_SESSION["authenticated"]))
    header("Location: index.html");

?>

<!DOCTYPE html>
<html>
<head>
	<!-- Mobile Specific Meta -->
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Favicon-->
	<link rel="shortcut icon" href="">
	<!-- Author Meta -->
	<meta name="author" content="Andres Solis Montero">
	<!-- Meta Description -->
	<meta name="description" content="">
	<!-- Meta Keyword -->
	<meta name="keywords" content="">
	<!-- meta character set -->
	<meta charset="UTF-8">
	<!-- Site Title -->
	<title>Microsoft Hearts</title>

	
		<!--
		CSS
        ============================================= -->
        <link rel="stylesheet" href="css/background.css?asdf">
        <link rel="stylesheet" href="vendor/bootstrap-4/css/bootstrap.min.css">
        <link rel="stylesheet" href="vendor/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="css/card.css?a">

	</head>
	<body>
            <nav class="navbar navbar-dark bg-dark">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        <span class="navbar-toggler-text">
                                <?php print sprintf('%s <small>(%s %s)</small>', 
                                                                  $_SESSION['username'],
                                                                  $_SESSION['first_name'], 
                                                                  $_SESSION['last_name']);?>
                        </span>
                       
                    </button>
            </nav>
            <div class="collapse" id="navbarToggleExternalContent">
                    <div class="bg-dark p-3">
                            <ul class="navbar-nav mr-auto">
                                    <li class="nav-item">
                                            <a id="update_profile" class="nav-link" href="#">Update Profile</a>
                                    </li>
                                    <li class="nav-item">
                                            <a id="rankings" class="nav-link" href="#">Rankings</a>
                                    </li>  
                                    <li class="nav-item">
                                            <a id="logout" class="nav-link" href="#">Log Out</a>
                                    </li>
                            </ul>   
                    </div>
            </div>
            
            <div id="container"></div>
            <br>
            <div id="controls"></div>


		<!-- jQuery first, then Tether, then Bootstrap JS. -->
                <script src="vendor/jquery3.2.1/jquery.min.js" ></script>
                <script src="vendor/popper1.12.9/popper.min.js"></script>
                
                <script src="vendor/bootstrap-4/js/bootstrap.min.js"></script>
                <script src="vendor/json2html-sprintf/sprintf.js"></script>
                <script src="vendor/json2html-sprintf/json2html.js"></script>
                <script src="vendor/json2html-sprintf/jquery.json2html.js"></script>
                <script src="vendor/jQuery-MD5/jquery.md5.js"></script>
                <script src="js/display.js?asasaadddassaasss"></script>	
                <script>
                    $(function() {
                        $('#update_profile').click(function(){
                            showProfile('#container');
                            $('#navbarToggleExternalContent').collapse('hide');
                        });
                        $('#rankings').click(function(){
                            showRankings('#container', "wins");
                            $('#navbarToggleExternalContent').collapse('hide');
                        })
                        $('#logout').click(function(){
                            logout();
                        });


                        var CURRENT_GAME = <?php print $_SESSION['CURRENT_GAME']; ?>;
                        

                        if (CURRENT_GAME == -1)
                        {
                            showHome(container);
                        } 
                        else
                        {
                            showGame('#container',CURRENT_GAME);
                            showGameControls('#controls');
                        }

                    });
                </script>
    </body>
</html>