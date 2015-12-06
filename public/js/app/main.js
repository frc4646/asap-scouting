function ajax (/*options*/) {
    "use strict";
    var options = {};
        options.callback = function (e) {
            $("img.response-img").prop("src", $("img.response-img").attr("data-success-url"));
            setTimeout(function () {
                $(".container-main").fadeOut("slow", function () {
                    success(e);
                });
            }, 500);
        },
        options.success = function () {
            var x = {
                main: $("h1.main-header").attr("data-response-url"),
                success: $("h1.main-header").attr("data-success-url")
            };
            $("h1.main-header").remove();
            $("div.container-main").prepend('<img class="response-img" src="' + x.main + '" height="50px" width="50px" data-success-url="' + x.success + '">');
            $("p.lead").text("Crunching the latest data...");
        }
    doPost($("div.container-main").attr("data-content-url"), null, "GET", "html", options.callback, options.success);
}


function detectMobile () {
    "use strict";
    var uagent = navigator.userAgent.toLowerCase();

   if (uagent.search("mobile") > -1) {
       return true
   } else {
       return false;
   }
}

function success (e) {
    "use strict";
    $("div#body-container").removeClass("container");
    $("div#body-container").addClass("container-fluid");
    $(".container-main").remove();
    $("div#replaceDiv").append(e);
    $("div.container-hold").fadeIn({
        start: function () {
            $("div.container-hold").removeAttr("style");
        }
    }, 500);
    $('#match-table').tablesorter({
        theme: 'bootstrap',
        headerTemplate: '{content} {icon}',
        widgets: ['filter'],
        /*widgetOptions: {
            //filter_external : '.tablesorter-filter',
            filter_columnFilters: false,
        }*/
    });
    doClick();
}

function main () {
    "use strict";
    var isMobile = detectMobile();

    console.log(isMobile);

    if (isMobile) {
        $("div#replaceDiv").alterClass("col-lg-6", "col-sm-12");
        ajax();
    } else {
        ajax();
    }
}
function doSet(e) {
    for (var i = 0; i <= 3; i++) {
        $("div#team-red-" + i).children("p").text(e["red" + i]);
        $("div#team-blue-" + i).children("p").text(e["blue" + i]);
    }

    if (!e["match"]) {
        $("div#match-num > p").text("---");
        console.log(e["match"]);
    } else if (e["match"]) {
        $("div#match-num > p").text(e["match"]);
        console.log(e["match"]);
    }

    if (!e["redscore"]) {
        $("div#match-red > p").text("---");
    } else if (e["redscore"]) {
        $("div#match-red > p").text(e["redscore"]);
    }
    if (!e["bluescore"]) {
        $("div#match-blue > p").text("---");
    } else if (e["bluescore"]) {
        $("div#match-blue > p").text(e["bluescore"]);
    }

    var n = 1,
        z = 1,
        color = "red",
        letter = "a",
        type = "score";
    for (var v = 1; v <= 6; v++) {
        if (v <= 3) {
            color = "red";
        } else if (v >= 4 && v <= 6) {
            color = "blue";
        }
        for (var x = 1; x <= 5; x++) {
            if (n > 3) {
                n = 1;
            }
            if (z > 3) {
                z = 1;
            }
            if (x <= 3) {
                type = "score";
            } else if (x >= 4 && x <= 5) {
                type = "penalty"
            }
            if (type == "penalty" && z > 2) {
                z = 1;
            }
            letter = String.fromCharCode(96 + z);
            /*console.log(e[color + n + type + letter]);
            console.log(color + n + type + letter + "###" + x + "###" + v + "###" + z);
            console.log("div#" + type + "-" + color + "-" + letter + "-" + n + " > p");*/
            if (!e[color + n + type + letter]) {
                $("div#" + type + "-" + color + "-" + letter + "-" + n + " > p").text("---");
            } else {
                $("div#" + type + "-" + color + "-" + letter + "-" + n + " > p").text(e[color + n + type + letter]);
            }
            z++;
        }
        n++;
    }
    /*
    if (v <= 3) {
            var color = "red";
        } else if (v > 3 && v <= 6) {
            var color = "blue";
        }
        if (n > 3){
            n = 1;
        }
        console.log(e[color + n + "score" + String.fromCharCode(96 + n)]);
        console.log(color + n + "score" + String.fromCharCode(96 + n));
        console.log("div#score-" + color + "-" + String.fromCharCode(96 + n) + "-" + n);
        $("div#score-" + color + "-" + String.fromCharCode(96 + n) + "-" + n).children("p").text(e[color + n + "score" + String.fromCharCode(96 + n)]);
        n++;*/
}
function doClick() {
    $("table#match-table tr").bind("click", function (e) {
        console.log(e);
        e.stopPropagation();
        console.info(e.currentTarget.getAttribute("data-row-number"));

        var row = e.currentTarget.getAttribute("data-row-number");

        console.log($("table#match-table").attr("data-response-url"));

        $.ajax({
            url: $("table#match-table").attr("data-response-url"),
            type: "POST",
            dataType: "json",
            beforeSend: function () {
                $(".loader").fadeIn(500);
            },
            data: {
                data_id: row,
                csrf_token: $("span.csrf_token").prop("id")
            },
            success: function (e) {
                $(".loader").fadeOut(500);
                console.log(e);
                doSet(e);
            }
        })
    });
}
$(document).ready(function () {
    "use strict";
    main();
});
