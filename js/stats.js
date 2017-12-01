/* 


__author__    = "Andrés Solis Montero"
__copyright__ = "Copyright 2017"
__version__   = "1.0"  
*/
$(function() {
    var options    = {
        "action": "stats",
        "column": "wins"
    };
    showStats('#rankings', options);
    // var options    = {
    //     "action": "game",
    //     "column": "wins",
    //     "game_id": 1
    // };
    // showGame('#rankings',options);
});