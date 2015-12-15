function hideTable () {
    $("div#replaceDiv").css("display", "none");
    $("div#manager").removeAttr("style");
    $("div#manager").css("padding-left", "0px");
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

function getTable (callNext = true) {
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
    $("div#replaceDiv").fadeIn(500);
    if (!detectMobile()) { // Not Yet Available on mobile
        $("div#manager").fadeIn(500);
        $('#match-table').tablesorter({
            theme: 'bootstrap',
            headerTemplate: '{content} {icon}',
            widgets: ['filter'],
        });
    }
    doClick();
}

function doSet(e) {
    $("div#manager").attr("data-match-id", e["match"]);
    for (var i = 0; i <= 3; i++) {
        $("div#team-red-" + i).children("span").text(e["red" + i]);
        $("div#team-blue-" + i).children("span").text(e["blue" + i]);
    }

    if (!e["match"]) {
        $("div#match-num > span").text("---");
    } else if (e["match"]) {
        $("div#match-num > span").text(e["match"]);
        $("div#manager").attr("data-match-id", e["match"]);
    }

    if (!e["redscore"]) {
        $("div#match-red > span").text("---");
    } else if (e["redscore"]) {
        $("div#match-red > span").text(e["redscore"]);
    }
    if (!e["bluescore"]) {
        $("div#match-blue > span").text("---");
    } else if (e["bluescore"]) {
        $("div#match-blue > span").text(e["bluescore"]);
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
                $("div#" + type + "-" + color + "-" + letter + "-" + n + " > span").text("---");
            } else {
                $("div#" + type + "-" + color + "-" + letter + "-" + n + " > span").text(e[color + n + type + letter]);
            }
            z++;
        }
        n++;
    }
}
function doDBLClick(x) {
    $("span.dblclick").dblclick(function (e) {
        e.stopImmediatePropagation();
        var value = $(this).text() || "";
        if (value == "---") {
            value = 0;
        }
        console.log(value);
        $(this).text("");
        $(this).append('<input type="text" class="inputData" value="' + value + '">');
        $('input.inputData').keypress(function (e) {
            if (e.which == 13) {
                $(this).focusout();
            }
        });
        $("input.inputData").focusout(function (e) {
            e.stopPropagation();
            var val = $(this).val() || "---",
                $is = $(this).parent("span.dblclick").parent("div");
            $(this).parent("span.dblclick").text(val);
            $(this).remove();
            var data = {},
                options = {
                callback: function (e) {
                    $(".loader").fadeOut(500);
                    clickHandler($("div#manager").attr("data-match-id"), true);
                },
                beforeSend: function () {
                    $(".loader").fadeIn(500);
                }
            };
            data["rowID"] = x.match;
            data["newValue"] = $(this).val() || 0;
            data["column"] = $is.prop("id").split("-")[1] + $is.prop("id").split("-")[3] + $is.prop("id").split("-")[0] + $is.prop("id").split("-")[2];
            data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
            doPost($("div#manager").attr("data-set-action"), data, "POST", "json", options.callback, options.beforeSend);
        });
    });
}
function clickHandler (e, doOptions = false) {
        if (!doOptions) {
            var row = e.currentTarget.getAttribute("data-row-number");
        } else {
            var row = e;
        }
        var data = {},
        options = {
            callback: function (e) {
                $(".loader").fadeOut(500);
                if (detectMobile()) {
                    doSet(e);
                    doDBLClick(e);
                    if (!doOptions) {
                        $("nav.navbar").addClass("nav-close");
                        $("body#body-frame").addClass("nav-body-close");
                        hideTable();
                    }
                } else {
                    doDBLClick(e);
                    doSet(e);
                }
            },
            beforeSend: function () {
                $(".loader").fadeIn(500);
            }
        };

    data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
    data["data_id"] = row;

    doPost($("table#match-table").attr("data-response-url"), data, "POST", "json", options.callback, options.beforeSend);
}
function doClick() {
    $("tr.body-row").click(function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
        console.log("Clicked")
        clickHandler(e);
    });
}
$(document).ready(function () {
    "use strict";
    getTable();
});
