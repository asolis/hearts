<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
if (empty($_SESSION["authenticated"]) or !$_SESSION['admin'])
    header("Location: index.php");

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
            .nb-card {
                color: rgb(200,200,200);
            }
        </style>
	</head>
	<body>
    <div class="nb-card">
    
                        <div class="card ">
                                <div class="card-header ">
                                    Administration Console
                                </div>
                                <div class="card-body ">
                                <form id="reset_password">
                                    Reset Password
                                    <div class="form-group mt-3">
                                        <label for="username">Username </label>
                                        <select class="form-control" name="user_id" id="users">
                                        
                                        </select>
                                    </div>
                                    
                                    
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="password" placeholder="New Password"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="hidden" class="form-control" name="action" value="reset_password" />
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary float-right" id="change">Change</button>
                                    </div>
                                </form> 
                                
                                <br><br><br><br><br>

                                <form id="unlock_game" class="mt-5">
                                    Unlock Game
                                    <div class="input-group mt-3">
                                        <input type="hidden" name="action" value="unlock_game"/>
                                    </div>
                                    <div class="input-group">
                                        
                                        <input type="text" name="game_id" class="form-control" placeholder="Game Gode">
                                        <span class="input-group-btn">
                                        <button class="btn btn-primary" type="submit" id="unlock">Unlock</button>
                                        </span>
                                    </div>
                                <br><br><br>
                                    <div class="input-group mt-3">
                                        <a id="sync">Sync</a>
                                    </div>
                                
                                <br><br><br>
                                    <div class="input-group mt-3">
                                        <a id="logout">Log Out</a>
                                    </div>

                                   
                                </form>   
                                </div>
                        </div>
                </div>
                
		<!-- jQuery first, then Tether, then Bootstrap JS. -->
                <script src="vendor/jquery3.2.1/jquery.min.js" ></script>
                <script src="vendor/popper1.12.9/popper.min.js"></script>
                
                <script src="vendor/bootstrap-4/js/bootstrap.min.js"></script>
                <script src="vendor/json2html-sprintf/sprintf.js"></script>
                <script src="vendor/json2html-sprintf/json2html.js"></script>
                <script src="vendor/json2html-sprintf/jquery.json2html.js"></script>
                <script src="vendor/jQuery-MD5/jquery.md5.js"></script>
                <script src="js/display.js"></script>	
                <script>
                        $(function() {
                        
                        var controller = "controller.php";
                        var options    = {
                            'action': 'list_users'
                        };
                        $.post(controller, options, function(json){
                            
                            var transform = {"<>":"option", "value":"${id}", "html":"${first_name} ${last_name} (${username})"};
                            $('#users').json2html(json.data, transform);
                        },'json');
                            
                        $('#change').click(function(event)
                        {
                            event.preventDefault();
                            var inputs = $('#reset_password').serializeArray();
                            for (var i = 0; i < inputs.length; i++)
							    if (inputs[i].name == 'password' && inputs[i].value)
                                    inputs[i].value = $.md5(inputs[i].value);
                            
                            $.post(controller, inputs, function(json){
                                alert(json.message);
                            },'json');

                        });

                        $('#unlock').click(function(event){
                            event.preventDefault();
                            var inputs = $('#unlock_game').serializeArray();
                            
                            $.post(controller, inputs, function(json){
                                alert(json.message);
                            },'json');
                            
                        });

                        $('#sync').click(function(event){
                            var options = {
                                "action": "sync"
                            };
                            $.post(controller, options, function(json){
                                alert(json.message);
                            },'json');
                        });
                           
                        $('#logout').click(function(event){
                            var options = {
                                "action": "logout"
                            }
                            $.post(controller, options, function(json){
                                if (json.return)
                                    window.location.href = 'index.php';
                            },'json');
                        });

                        });
                </script>

    </body>
</html>
