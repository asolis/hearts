<?php
/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once('authentication.php');
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
                                            <a class="nav-link" href="#">Update Profile</a>
                                    </li> 
                                    <li class="nav-item">
                                            <a class="nav-link" href="?action=logout">Log Out</a>
                                    </li>
                            </ul>   
                    </div>
            </div>
            
            <div id="container"></div>


		<!-- jQuery first, then Tether, then Bootstrap JS. -->
                <script src="vendor/jquery3.2.1/jquery.min.js" ></script>
                <script src="vendor/popper1.12.9/popper.min.js"></script>
                
                <script src="vendor/bootstrap-4/js/bootstrap.min.js"></script>
                <script src="vendor/json2html-sprintf/sprintf.js"></script>
                <script src="vendor/json2html-sprintf/json2html.js"></script>
                <script src="vendor/json2html-sprintf/jquery.json2html.js"></script>
                <script src="vendor/jQuery-MD5/jquery.md5.js"></script>
                <script src="js/display.js?aass"></script>	
                <script>
                    $(function() {

function showProfile(container)
{
    function update(obj)
    {
        var inputs = $(sprintf('$s form', container)).serializeArray();

        for (var i = 0; i < inputs.length; i++)
            if (inputs[i].name == 'password')
                inputs[i].value = $.md5(inputs[i].value);

        $.post(controller, inputs, function(output)
        {
            if (output)
            {
                var json = JSON.parse(output);
               // window.location.href = window.location.href;
                console.log(json);
            }
        });
        
        obj.event.preventDefault();
    }

    function set_invalid(container, input_name, message)
    {
        var input = $(sprintf("$s form input[name='$s']", container, input_name));
        var feedback = input.next();
        input.addClass('is-invalid');
        feedback.html(message);
    }

    var transform = {"<>":"div","class":"nb-card","html":[
        {"<>":"div","class":"card ","html":[
            {"<>":"div","class":"card-header ","html":[
                {"<>":"span","html":"Profile Information"}
              ]},
            {"<>":"div","class":"card-body tab-content d-flex h-100","html":[
                {"<>":"div","class":"tab-pane w-100 active align-self-center", "role":"tabpanel","children":[
                    {"<>":"form", "novalidate":"", "html":[
                        {"<>":"div","class":"form-group row","html":[
                            {"<>":"label","class":"col-sm-3 col-form-label","html":"First Name"},
                            {"<>":"div","class":"col-sm-9","html":[
                                {"<>":"input", "class":"form-control", "type":"text","name":"first_name","value":"${first_name}", "required":"required"},
                                {"<>":"div",   "class":"invalid-feedback", "html":""}
                              ]}
                          ]},
                        {"<>":"div","class":"form-group row","html":[
                            {"<>":"label","class":"col-sm-3 col-form-label","html":"Last Name"},
                            {"<>":"div","class":"col-sm-9","html":[
                                {"<>":"input", "class":"form-control", "type":"text","name":"last_name","value":"${last_name}", "required":"required"},
                                {"<>":"div", "class":"invalid-feedback", "html":""}
                              ]}
                          ]},
                        {"<>":"div","class":"form-group row","html":[
                            {"<>":"label","class":"col-sm-3 col-form-label","html":"Username"},
                            {"<>":"div","class":"col-sm-9","html":[
                                {"<>":"input", "class":"form-control","type":"text","name":"username","value":"${username}", "required":"required"},
                                {"<>":"div", "class":"invalid-feedback", "html":""}
                              ]}
                          ]},
                        {"<>":"div","class":"form-group row","html":[
                            {"<>":"label","class":"col-sm-3 col-form-label","html":"Password"},
                            {"<>":"div","class":"col-sm-9","html":[
                                {"<>":"input", "class":"form-control" ,"type":"password","name":"password","value":"","required":"required"},
                                {"<>":"div", "class":"invalid-feedback", "html":""}
                              ]}
                          ]},
                        {"<>":"div","class":"form-group","html":[
                            {"<>":"input","type":"hidden","name":"action","value":"update_profile","class":"form-control"}
                          ]},
                        {"<>":"br"},
                        {"<>":"button","id":"update","type":"submit","class":"btn btn-primary btn-block btn-large","html":"Update", "onclick":update}
                      ]}
                ]}
              ]}
          ]}
      ]};
      
      $.post(controller, {'action':'profile'}, function(output)
      { 
          if (!output)
            return;
          var json = JSON.parse(output);

          if (json.return)
          {
            $(container).html('');
            $(container).json2html(json.data, transform);
            set_invalid(container, 'first_name', "helo");
          }
      });
      
}





                        
                        showProfile('#container');
                        
                    });
                </script>
    </body>
</html>