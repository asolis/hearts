/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
var controller = 'controller.php';


function input_invalid(container, input_name, message)
{
    var input = $(sprintf("$s form input[name='$s']", container, input_name));
    var feedback = input.next();
    input.addClass('is-invalid');
    feedback.html(message);
}
function clearFormErrMsgs(container)
{
    $(sprintf('$s form input.is-invalid', container)).removeClass('is-invalid');
    $(sprintf('$s form div.invalid-feedback', container)).html('');
}
function setFormErrMsgs(container, messages)
{
    $.each(messages, function(column, msg){
        input_invalid(container, column, msg);
    });
}

function logout()
{
    var options = {
        "action": "logout"
    };
    $.post(controller, options, function(json){
        if (json.return)
            location.reload();
    },'json');
}


function showProfile(container)
{
    function updateProfile(obj)
    {
        obj.event.preventDefault();

        var inputs = $(sprintf('$s form', container)).serializeArray();

        for (var i = 0; i < inputs.length; i++)
            if (inputs[i].name == 'password' && inputs[i].value)
                inputs[i].value = $.md5(inputs[i].value);

        $.post(controller, inputs, function(json)
        {
            clearFormErrMsgs(container);
            if (!json.return)
                setFormErrMsgs(container, json.message);
            else
            {
                window.location.href = 'home.php';
            }
        }, 'json');
    }

    
    var transform = {"<>":"div","class":"nb-card","html":[
        {"<>":"div","class":"card ","html":[
            {"<>":"div","class":"card-header ","html":[
                {"<>":"span","html":"Profile Information"}
              ]},
            {"<>":"div","class":"card-body tab-content d-flex h-100","html":[
                {"<>":"div","class":"tab-pane w-100 active align-self-center pt-5", "role":"tabpanel","children":[
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
                        {"<>":"button","id":"update","type":"submit","class":"btn btn-primary btn-block btn-large","html":"Update", "onclick":updateProfile}
                      ]}
                ]}
              ]}
          ]}
      ]};
      
      $.post(controller, {'action':'profile'}, function(json)
      { 
          if (json.return)
          {
            $(container).html('');
            $(container).json2html(json.data, transform);    
          }
      },'json');
      
}


function showRankings(container, stats_column)
{
    var options    = {
        "action": "stats",
        "column": stats_column
    };

    $.getJSON(controller, options, function(data){
        
        var transforms = {
            "card"  :{"<>":"div","class":"container mt-5","html":[
                {"<>":"div","class":"card tex ","html":[
                    {"<>":"div","class":"card-header ","html":[
                        {"<>":"h5","html":sprintf("Rankings  <small>| $s</small> ", options.column)}
                    ]},
                    {"<>":"div","class":"card-body text-center card-body-no-padding","html":[
                        {"<>":"table","class":"table table-sm table-bordered table-dark ","html":[
                            {"<>":"thead","html":[
                                {"<>":"tr","html":[
                                    {"<>":"th","html":"Rank"},
                                    {"<>":"th","html":"Player"},
                                    {"<>":"th", "onclick":function(e){ 
                                        (container, 'wins');
                                    }, "children":[{"<>":"a", "href":"#","html":"W"}]},
                                    {"<>":"th", "onclick":function(e){ 
                                        (container, 'losses');
                                    }, "children":[{"<>":"a", "href":"#","html":"L"}]},
                                    {"<>":"th", "onclick":function(e){ 
                                        (container, 'plays');
                                    }, "children":[{"<>":"a", "href":"#","html":"P"}]},
                                    {"<>":"th", "onclick":function(e){
                                        (container, 'shots');
                                    }, "children":[{"<>":"a", "href":"#","html":"S"}]},
                                    {"<>":"th", "onclick":function(e){ 
                                        (container, 'cheats');
                                    }, "children":[{"<>":"a", "href":"#","html":"C"}]}
                                  ]}
                              ]},
                            {"<>":"tbody","html": function(obj){
                                return $.json2html(obj.data, transforms.rows);
                            }}
                          ]}
                      ]}
                  ]}
              ]},
            "rows": {"<>":"tr","html":[
                {"<>":"th","html":"${rank}"},
                {"<>":"td","html":"${first_name} ${last_name}"},
                {"<>":"td","html":"${wins}"},
                {"<>":"td","html":"${losses}"},
                {"<>":"td","html":"${plays}"},
                {"<>":"td","html":"${shots}"},
                {"<>":"td","html":"${cheats}"}
              ]}
        };

        $(container).html('');
        $(container).json2html(data, transforms.card);
    });
}


function showGame(container, game_id)
{
    var options    = {
        "action": "game",
        "game_id": game_id
    };
    

    $.getJSON(controller, options, function(data){
        
        var transforms = {
            "card":{"<>":"div","class":"container mt-3","html":[
                {"<>":"div","class":"card tex ","html":[
                    {"<>":"div","class":"card-header ","children":[
                        {"<>":"div","html":"Game Code: ${game_id}"}
                    ]},
                    {"<>":"div","class":"card-body text-center card-body-no-padding","html":[
                        {"<>":"table","class":"table table-sm table-bordered table-dark","html":[
                            {"<>":"thead","html":[
                                {"<>":"tr","html":[
                                    {"<>":"th","html":"#"},
                                    {"<>":"th","html":"${players.player1}"},
                                    {"<>":"th","html":"${players.player2}"},
                                    {"<>":"th","html":"${players.player3}"},
                                    {"<>":"th","html":"${players.player4}"}
                                ]},
                                {"<>":"tr", "class":"total-score","html":[
                                    {"<>":"td","html":"${scores.index}"},
                                    {"<>":"td","html":"${scores.player1_score}"},
                                    {"<>":"td","html":"${scores.player2_score}"},
                                    {"<>":"td","html":"${scores.player3_score}"},
                                    {"<>":"td","html":"${scores.player4_score}"}
                                ]}
                            ]},
                            {"<>":"tbody","children":function(obj){
                                
                                return $.json2html(obj.hands, transforms.row);
                            }}
                        ]}
                    ]}
                ]}
            ]},
            "scores": {"<>":"tr","html":[
                {"<>":"td","html":"${index}"},
                {"<>":"td","html":"${player1_score}"},
                {"<>":"td","html":"${player2_score}"},
                {"<>":"td","html":"${player3_score}"},
                {"<>":"td","html":"${player4_score}"}
            ]},
            "row": {"<>":"tr","html":[
                {"<>":"th","html":"${index}"},
                {"<>":"td","html":"${player1_score}"},
                {"<>":"td","html":"${player2_score}"},
                {"<>":"td","html":"${player3_score}"},
                {"<>":"td","html":"${player4_score}"}
            ]}
        };
        $(container).html('');
        $(container).json2html(data.data, transforms.card);
    });
}


function showHome(container)
{
    function newGame(event)
    {
        var options = {
            'action': 'create',
        };
        $.post(controller, options, function(data){
            if (data.return)
                location.reload();
        },'json');
    }
    function joinGame(event)
    {
        var options = {
            'action': 'join',
            'game_id': $('#join_game_id').val()
        };
        $.post(controller, options, function(json){
            console.log(json);
            if (json.return)
                location.reload();
            else
            {
                var a = 3;
                console.log(sprintf('div.invalid-feedback-wa', container));
                $('#join_game_id').addClass('is-invalid');
                $('div.invalid-feedback-wa').html(json.message);
            }
            
        },'json');
    }
    var transform = {"<>":"div","class":"container","html":[
        {"<>":"div","class":"card m-2 ","html":[
            {"<>":"div","class":"card-body p-5 ","html":[
                {"<>":"button","id":"login","class":"btn btn-primary btn-block btn-large","html":"New Game","onclick":newGame},
                {"<>":"br","html":""},
                {"<>":"div","class":"input-group","html":[
                    {"<>":"input","id":"join_game_id","type":"text","pattern":"\\d*", "class":"form-control","placeholder":"Game Code"},
                    {"<>":"span","class":"input-group-btn","html":[
                        {"<>":"button","class":"btn btn-primary form-control","type":"button","html":"Join Game","onclick":joinGame}
                      ]}
                  ]},
                  {'<>':"div",  "class":"invalid-feedback-wa"}
              ]}
          ]}
      ]};
      $(container).html('');
      $(container).json2html([1], transform);
}


function showGameControls(container, game_id)
{
    function addHand(obj)
    {
            obj.event.preventDefault();
            var selects   = $(sprintf('$s form',container)).serializeArray();

            $(sprintf("$s form select",container)).removeClass('is-invalid');
            
            $.post(controller,selects, function(json){
                
                if (json.return)
                    location.reload();
                $(sprintf("$s form select",container)).addClass('is-invalid');
              
            },'json');
    };
    function finishGame(obj)
    {
            var options = {
                "action":"finish"
            };
           
            if (confirm('Would you like to finish this game?'))
            {
                
                $.post(controller,options, function(json){
                   
                    if (json.return){
                        
                        location.reload();
                    }
                        
                },'json');
            }  
    };
    function lastHand(obj)
    {
            var options = {
                "action":"last_hand"
            };
            
            if (confirm('Would you like to remove the last played hand?'))
            {
                $.post(controller,options, function(json){
                    
                    if (json.return){
                       
                        location.reload();
                    }
                },'json');
            }
            
    }


    

     var transforms = {
             'control':{"<>":"footer","class":"fixed-bottom","html":[
                {"<>":"div","id":"control_view","class":"collapse","html":[
                    {"<>":"div", "class":"container", "html":[
                        {"<>":"form","html":[
                                {"<>":"div","class":"row no-gutters","html":function(obj){
                                        return $.json2html(obj.data, transforms.cols);
                                }},
                                {"<>":"div","class":"form-group row","html":[
                                {"<>":"div","class":"col-sm-12","html":[
                                        {"<>":"button","type":"submit","class":"btn btn-primary btn-block btn-large","html":"Add Hand","onclick":addHand}
                                        ]}
                                ]},
                                {"<>":"div","class":"form-group row","html":[
                                        {"<>":"input","type":"hidden","name":"action","value":"add_hand","class":"form-control"}
                                ]}
                        ]},
                        {"<>":"div","class":"form-group row","html":[
                        {"<>":"div","class":"col-sm-12","html":[
                                {"<>":"button","class":"btn btn-danger btn-block btn-large","html":"Remove Last Hand","onclick":lastHand}
                                ]}
                        ]}
                    ]}
                ]},
                {"<>":"div","class":"container mt-3 mb-3","html":[
                    {"<>":"button","class":"btn btn-light","data-toggle":"collapse","href":"#control_view","html":"Game Controls"},
                    {"<>":"button","class":"btn btn-success float-right","html":"Finish Game","onclick":finishGame}
                  ]}
              ]},
             'cols' : {"<>":"div","class":"col","html":[
                     {"<>":"div","class":"form-group text-center","html":[
                             {"<>":"label","class":"control-label","html":"${username}"},
                             {"<>":"select","class":"form-control","name":"${label}","html":function(e){
                                     return $.json2html(Array.from(Array(27).keys()),transforms.options);
                             }}
                     ]}
             ]},
             'options':  {"<>":"option","html":"$d", "value":"$d"}
     };
     var options = {
         'action':"controls",
         'game_id':  game_id
     };
     
     $.post(controller, options, function(json){
        console.log(json);
        if (json.return)
            $(container).json2html(json,transforms.control);
        if (json.finished)
        {
            $(sprintf('$s button',container)).prop('disabled',true).removeClass('btn-success').removeClass('btn-light');
        }
            
    },'json');
     
}

