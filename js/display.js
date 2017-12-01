/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
var controller = 'controller.php';

function showStats(container, options)
{
    $.getJSON(controller, options, function(data){
        
        var transforms = {
            "card"  :{"<>":"div","class":"container mt-5","html":[
                {"<>":"div","class":"card tex ","html":[
                    {"<>":"div","class":"card-header ","html":[
                        {"<>":"h5","html": sprintf("Rankings  <small>| $s</small> ", options.column)}
                      ]},
                    {"<>":"div","class":"card-body text-center","html":[
                        {"<>":"table","class":"table table-sm table-bordered table-dark","html":[
                            {"<>":"thead","html":[
                                {"<>":"tr","html":[
                                    {"<>":"th","html":"Rank"},
                                    {"<>":"th","html":"Player"},
                                    {"<>":"th", "onclick":function(e){ 
                                        options.column = 'wins';
                                        showStats(container, options);
                                    }, "children":[{"<>":"a", "href":"#","html":"W"}]},
                                    {"<>":"th", "onclick":function(e){ 
                                        options.column = 'losses';
                                        showStats(container, options);
                                    }, "children":[{"<>":"a", "href":"#","html":"L"}]},
                                    {"<>":"th", "onclick":function(e){ 
                                        options.column = 'plays';
                                        showStats(container, options);
                                    }, "children":[{"<>":"a", "href":"#","html":"P"}]},
                                    {"<>":"th", "onclick":function(e){ 
                                        options.column = 'shots';
                                        showStats(container, options);
                                    }, "children":[{"<>":"a", "href":"#","html":"S"}]},
                                    {"<>":"th", "onclick":function(e){ 
                                        options.column = 'cheats';
                                        showStats(container, options);
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
                {"<>":"td","html":"${username} (${first_name} ${last_name})"},
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


function showGame(container, options)
{
    $.getJSON(controller, options, function(data){
        
        var transforms = {
            "card":{"<>":"div","class":"container mt-5","html":[
                {"<>":"div","class":"card tex ","html":[
                    {"<>":"div","class":"card-header ","html":[
                        {"<>":"span", "class":"float-right fa fa-refresh", "html":"", "aria-hidden":"true", "onclick":function(e){
                            showGame(container, options);
                        }},
                        {"<>":"h5","html":"Game Code: ${game_id}"}
                    ]},
                    {"<>":"div","class":"card-body text-center","html":[
                        {"<>":"table","class":"table table-sm table-bordered table-dark","html":[
                            {"<>":"thead","html":[
                                {"<>":"tr","html":[
                                    {"<>":"th","html":"#"},
                                    {"<>":"th","html":"Player1"},
                                    {"<>":"th","html":"Player2"},
                                    {"<>":"th","html":"Player3"},
                                    {"<>":"th","html":"Player4"}
                                ]},
                                {"<>":"tr","html":[
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