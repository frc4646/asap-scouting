function detectMobile () {
    "use strict";
    var uagent = navigator.userAgent.toLowerCase();

   if (uagent.search("mobile") > -1) {
       return true
   } else {
       return false;
   }
}

function getTable () {
    "use strict";
    var options = {
        callback: function (e) {
            $("img.response-img").prop("src", $("img.response-img").attr("data-success-url"));
            setTimeout(function () {
                $(".container-main").fadeOut("slow", function () {
                    success(e);
                });
            }, 500);
        },
        beforeSend: function () {
            $("div.container-main").prepend('<img class="response-img" src="' + $("h1.main-header").attr("data-response-url") + '" height="50px" width="50px" data-success-url="' + $("h1.main-header").attr("data-success-url") + '">');
            $("h1.main-header").remove();
            $("p.lead").text("Crunching the latest data...");
        }
    };
    doPost($("div.container-main").attr("data-content-url"), null, "GET", "html", options.callback, options.beforeSend);
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
        /*sortAppend: {
          3 : [[ 4,'s' ], [ 5,'s' ]], // group columns 3-5
          4 : [[ 3,'s' ], [ 5,'s' ]], // ('s'ame direction)
          5 : [[ 3,'s' ], [ 4,'s' ]],

          6 : [[ 7,'o' ]], // group columns 6-7
          7 : [[ 6,'o' ]]  // ('o'pposite direction)
        }*/
    });
    doClick();
}

function main () {
    "use strict";
    var isMobile = detectMobile();

    if (isMobile) {
        $("div#replaceDiv").alterClass("col-lg-6", "col-sm-12");
        getTable();
    } else {
        getTable();
    }
}
function doSet(e) {
    for (var i = 0; i <= 3; i++) {
        $("div#team-red-" + i).children("p").text(e["red" + i]);
        $("div#team-blue-" + i).children("p").text(e["blue" + i]);
    }

    if (!e["match"]) {
        $("div#match-num > p").text("---");
    } else if (e["match"]) {
        $("div#match-num > p").text(e["match"]);
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
            if (!e[color + n + type + letter]) {
                $("div#" + type + "-" + color + "-" + letter + "-" + n + " > p").text("---");
            } else {
                $("div#" + type + "-" + color + "-" + letter + "-" + n + " > p").text(e[color + n + type + letter]);
            }
            z++;
        }
        n++;
    }
}
function doDBLClick(x) {
    $("p.dblclick").dblclick(function (e) {
        e.stopImmediatePropagation();
        var value = $(this).text() || "";
        $(this).text("");
        $(this).append('<input type="text" class="inputData" value="' + value + '">');
        $("input.inputData").focusout(function (e) {
            e.stopPropagation();
            var val = $(this).val() || "---",
                $is = $(this).parent("p.dblclick").parent("div");
            $(this).parent("p.dblclick").text(val);
            $(this).remove();
            var data = {},
                options = {
                callback: function (e) {
                    $(".loader").fadeOut(500);
                },
                beforeSend: function () {
                    $(".loader").fadeIn(500);
                }
            };
            data["rowID"] = x.match;
            data["newValue"] = $(this).val() || 0;
            //console.log($(this));
            data["column"] = $is.prop("id").split("-")[1] + $is.prop("id").split("-")[3] + $is.prop("id").split("-")[0] + $is.prop("id").split("-")[2];
            data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
            doPost($("div#manager").attr("data-set-action"), data, "POST", "json", options.callback, options.beforeSend);
        });
    });
}
function doClick() {
    $("table#match-table tr").click(function (e) {
        e.stopPropagation();

        var row = e.currentTarget.getAttribute("data-row-number"),
            data = {},
            options = {
                callback: function (e) {
                    $(".loader").fadeOut(500);
                    doSet(e);
                    doDBLClick(e);
                },
                beforeSend: function () {
                    $(".loader").fadeIn(500);
                }
            };

        data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
        data["data_id"] = row;

        doPost($("table#match-table").attr("data-response-url"), data, "POST", "json", options.callback, options.beforeSend);
    });
}
$(document).ready(function () {
    "use strict";
    main();
});
