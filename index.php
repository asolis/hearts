<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
if (!empty($_SESSION["authenticated"]))
    header("Location: home.php");

?>
<!DOCTYPE html>
<html lang="zxx" class="no-js">
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
	</head>
	<body>
			<div class="nb-card">

					<div class="card ">
							<div class="card-header ">
							  <ul class="nav nav-tabs card-header-tabs">
								<li class="nav-item">
								  <a class="nav-link active" data-toggle="tab" href="#login_form" role="tab">Sign in</a>
								</li>
								<li class="nav-item">
								  <a class="nav-link" data-toggle="tab" href="#register_form" role="tab">Register</a>
								</li>
								
							  </ul>
							</div>
							<div class="card-body tab-content d-flex h-100">
											<div id="login_form" class="tab-pane active w-100 align-self-center" role="tabpanel">
													
													<!-- <div class="alert alert-danger alert-dismissible fade show" role="alert">
															<strong>Holy guacamole!</strong> You should check in on some of those fields below.
															<button type="button" class="close" data-dismiss="alert" aria-label="Close">
															  <span aria-hidden="true">&times;</span>
															</button>
													</div> -->
													<form >
															<!-- Username -->
															<div class="form-group">
																	<input type="text" 	autocapitalize="none"	name="username"   placeholder="Username"  	required="required" class="form-control"/>
																	<div class="invalid-feedback"></div>
															</div>
															<!-- Password -->
															<div class="form-group">
																	<input type="password" 	name="password"   placeholder="Password" 	required="required" class="form-control"/>
																	<div class="invalid-feedback"></div>
															</div>
															<div class="form-group">
																	<input type="hidden"	name="action"	  value="login" class="form-control"/>
															</div>
															<br>
															
															<!-- Button -->
															<button id="login" type="submit" class="btn btn-primary btn-block btn-large">Let me in</button>
													</form>
															<hr class="divider">

															<div class="text-center p-2 small">
																	<a href="game.html">Watch Game</a>>
															</div>
															<div class="text-center p-2 small">
																<a href="stats.html">Rankings</a>>
															</div>
															
														
													
											</div>

											<div id="register_form" class="tab-pane w-100 pt-5"  role="tabpanel">
													<form novalidate="" >
															<div class="form-group row">
															   <!-- <label class="col-sm-3  col-form-label">First Name</label> -->
															   <div class="col-sm-12">
																  <input class="form-control" type="text" name="first_name" value="" placeholder="First Name" required="required">
																  <div class="invalid-feedback"></div>
															   </div>
															</div>
															<div class="form-group row">
															   <!-- <label class="col-sm-3 col-form-label">Last Name</label> -->
															   <div class="col-sm-12">
																  <input class="form-control" type="text" name="last_name" value="" placeholder="Last Name" required="required">
																  <div class="invalid-feedback"></div>
															   </div>
															</div>
															<div class="form-group row">
															   <!-- <label class="col-sm-3 col-form-label">Username</label> -->
															   <div class="col-sm-12">
																  <input class="form-control" type="text" name="username" value="" placeholder="Username" required="required">
																  <div class="invalid-feedback"></div>
															   </div>
															</div>
															<div class="form-group row">
															   <!-- <label class="col-sm-3 col-form-label">Password</label> -->
															   <div class="col-sm-12">
																  <input class="form-control" type="password" name="password" value="" placeholder="Password" required="required">
																  <div class="invalid-feedback"></div>
															   </div>
															</div>
															<div class="form-group"><input type="hidden" name="action" value="register" class="form-control"></div>
															<br><br><button id="register" type="submit" class="btn btn-primary btn-block btn-large">Sign me Up!</button>
													</form>
											</div>
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
		<!-- Login  -->
		<script>
				$(function() {
					
					$('#login').click(function(event){
						event.preventDefault();

						var container = '#login_form';
						
						var inputs = $(sprintf('$s form', container)).serializeArray();
	
						for (var i = 0; i < inputs.length; i++)
							if (inputs[i].name == 'password' && inputs[i].value)
								inputs[i].value = $.md5(inputs[i].value);
						
						$.post(controller, inputs, function(json){
							
							clearFormErrMsgs(container);
							
							if (!json.return)
								setFormErrMsgs(container, json.message);
							else
							{
								window.location.href = 'home.php';
								
							}
						},'json');
					});
					
				});
			</script>
		<!-- Register  -->
		<script>
			$(function() {
				var controller = "controller.php";
				

				$('#register').click(function(event){
					
					event.preventDefault();

					var container = '#register_form';
					
					var inputs = $(sprintf('$s form', container)).serializeArray();

					for (var i = 0; i < inputs.length; i++)
						if (inputs[i].name == 'password' && inputs[i].value)
							inputs[i].value = $.md5(inputs[i].value);
					
					$.post(controller, inputs, function(json){
						
							clearFormErrMsgs(container);

							

							if (!json.return)
								setFormErrMsgs(container, json.message);
							else
							{
								
								location.reload();
								
							}
					},'json');
					
				});
				
			});
		</script>
	  </body>
</html>
