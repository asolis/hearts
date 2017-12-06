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
    header("Location: index.php");
if ($_SESSION['admin'])
    header("Location: admin.php");
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
	<title>Hearts</title>

	
		<!--
		CSS
        ============================================= -->
        <link rel="stylesheet" href="css/background.css">
        <link rel="stylesheet" href="vendor/bootstrap-4/css/bootstrap.min.css">
        <link rel="stylesheet" href="vendor/font-awesome-4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="css/card.css">
        <style>
                
               
               
        </style>
	</head>
	<body class="fireworks">
            <nav class="navbar fixed-top navbar-dark bg-dark">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggleExternalContent" >
                        <span class="navbar-toggler-icon"></span>
                        <a class="navbar-brand" href="#">
                            <?php 
                                            print sprintf('%s <small>(%s %s)</small>', 
                                                                  $_SESSION['username'],
                                                                  $_SESSION['first_name'], 
                                                                  $_SESSION['last_name']);
                            ?>
                        </a>
                    </button>
            </nav>
            <div class="collapse fixed-top" id="navbarToggleExternalContent">
                    <div class="bg-dark p-3">
                            <ul class="navbar-nav mr-auto">
                                    <li class="nav-item">
                                            <a class="nav-link" href="home.php">Home</a>
                                    </li>
                                    <li class="nav-item">
                                            <a class="nav-link" href="game.html">Watch Game</a>
                                    </li>
                                    <li class="nav-item">
                                            <a id="rankings" class="nav-link" href="#">Rankings</a>
                                    </li> 
                                    <li class="nav-item">
                                            <a id="update_profile" class="nav-link" href="#">Update Profile</a>
                                    </li> 
                                    <li class="nav-item">
                                            <a id="logout" class="nav-link" href="#">Log Out</a>
                                    </li>
                                    
                            </ul>   
                    </div>
            </div>
            
            <div id="container"></div>
           

            <div id="controls"></div>


		<!-- jQuery first, then Tether, then Bootstrap JS. -->
                <script src="vendor/jquery3.2.1/jquery.min.js" ></script>
                <script src="vendor/popper1.12.9/popper.min.js"></script>
                
                <script src="vendor/bootstrap-4/js/bootstrap.min.js"></script>
                <script src="vendor/json2html-sprintf/sprintf.js"></script>
                <script src="vendor/json2html-sprintf/json2html.js"></script>
                <script src="vendor/json2html-sprintf/jquery.json2html.js"></script>
                <script src="vendor/jQuery-MD5/jquery.md5.js"></script>
                <script src="vendor/confetti/jquery.confetti.js?asdaasdfsdfasdffasdasdf"></script>
                <script src="vendor/fireworks/jquery.fireworks.js"></script>
                <script src="js/display.js?asdfasdasdfasdasasdfasddff"></script>	
                <script>
                    $(function() {
                        $('#update_profile').click(function(){
                            showProfile('#container');
                            $('#controls').html('');
                            $('#navbarToggleExternalContent').collapse('hide');
                        });
                        $('#rankings').click(function(){
                            showRankings('#container', "wins");
                            $('#controls').html('');
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
                            showGame('#container',CURRENT_GAME, true);
                            showGameControls('#controls',CURRENT_GAME);

                        }
                        
                        
                    });
                </script>
    </body>
</html>