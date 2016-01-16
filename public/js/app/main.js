function hideTable() {
    "use strict";
    $("div#replaceDiv").css("display", "none");
    $("div#manager").removeAttr("style");
    $("div.back-button").removeAttr("style");
    $("div#manager").css("padding-left", "0px");
}

function detectMobile() {
    "use strict";
    var uagent = navigator.userAgent.toLowerCase();

    if (uagent.search("mobile") > -1) {
        return true;
    } else {
        return false;
    }
}

function getTable() {
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

function success(e) {
    "use strict";
    $("div#body-container").removeClass("container");
    $("div#body-container").addClass("container-fluid");
    $(".container-main").remove();
    $("div#replaceDiv").append(e);
    $("div#replaceDiv").fadeIn(500);
    if (!detectMobile()) { // Not Yet Available on mobile
        $("div#manager").fadeIn(500);
        $("#match-table").tablesorter({
            theme: "bootstrap",
            headerTemplate: "{content} {icon}",
            widgets: ["filter"]
        });
    }
    doClick();
}

function doSet(e) {
    "use strict";
    $("div#manager").attr("data-match-id", e.match);
    for (var i = 0; i <= 3; i++) {
        $("div#team-red-" + i).children("span").text(e["red" + i]);
        $("div#team-red-" + i).attr("data-team-num", e["red" + i]);
        $("div#team-blue-" + i).children("span").text(e["blue" + i]);
        $("div#team-blue-" + i).attr("data-team-num", e["blue" + i]);
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
function doExplodedSet(e, color, num) {
    $("p.exploded-match-text").text(e.match);

    $("div.exploded-team").addClass("team-" + color);
    //blue1scorea
    $("p.exploded-score-a-val").text(e[color + num + "scorea"]);
    $("p.exploded-score-b-val").text(e[color + num + "scorea"]);
    $("p.exploded-score-c-val").text(e[color + num + "scorec"]);
}
function doDBLClick(x) {
        $("div.team-num").click(function (e) {
            console.log("clicked");
            var color = $(this).prop("id").split("-")[1],
                num = $(this).prop("id").split("-")[2];

            if (!detectMobile()) {
                $("button.toggle-defense").off("click");
                var number = $(this).attr("data-team-num"),
                    dart = {};
                $("span.team-id").text("Team " + number);
                $("div#team-options").fadeIn(500);
                dart[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
                doPost(
                    $("div.team-details").attr("data-call-url").split(":team").join(number),
                    null,
                    "GET",
                    "json",
                    function (x) {
                        if (x.success) {
                            doPost(
                                $("div.team-details").attr("data-get-url").split(":team").join(number),
                                null,
                                "GET",
                                "json",
                                function (x) {
                                    if (!x.runOnce) {
                                        $("button.toggle-defense").each(function () {
                                            var tf = x["defences"][$(this).prop("id").split("-")[1]];
                                            if (tf == "true") {
                                                $(this).alterClass("btn-*", "btn-success");
                                            } else if (tf == "false") {
                                                $(this).alterClass("btn-*", "btn-danger");
                                            }
                                        });
                                    } else {
                                        $("button.toggle-defense").each(function () {
                                            $(this).alterClass("btn-*", "btn-info");
                                        });
                                    }
                                }
                            );
                        }
                    }
                );

                $("button.toggle-defense").click(function () {
                    dart["item"] = $(this).prop("id").split("-")[1]
                    if ($(this).hasClass("btn-info")) {
                        dart["val"] = true
                        $(this).alterClass("btn-*", "btn-success");
                    } else if ($(this).hasClass("btn-success")) {
                        dart["val"] = false
                        $(this).alterClass("btn-*", "btn-danger");
                    } else if ($(this).hasClass("btn-danger")) {
                        dart["val"] = true
                        $(this).alterClass("btn-*", "btn-success");
                    }
                    doPost(
                        $("div.team-details").attr("data-set-url").split(":team").join(number).split(":item").join("defences"),
                        dart,
                        "POST",
                        "json",
                        function (x) {
                            $(".loader").fadeOut(500);
                            if (!x.runOnce) {
                                $("button.toggle-defense").each(function () {
                                    var tf = x["defences"][$(this).prop("id").split("-")[1]];
                                    if (tf == "true") {
                                        $(this).alterClass("btn-*", "btn-success");
                                    } else if (tf == "false") {
                                        $(this).alterClass("btn-*", "btn-danger");
                                    }
                                });
                            } else {
                                $("button.toggle-defense").each(function () {
                                    $(this).alterClass("btn-*", "btn-info");
                                });
                            }
                        },
                        function () {
                            $(".loader").fadeIn(500);
                        }
                    );
                });
            } else if (detectMobile()) {
                $("div#manager").css("display", "none");

                var data = {},
                    options = {
                        callback: function (e) {
                            $(".loader").fadeOut(500);
                            doExplodedSet(e, color, num)
                            console.log(e);
                            console.log(color);
                        },
                        beforeSend: function () {
                            $(".loader").fadeIn(500);
                        }
                    };

                data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
                data["data_id"] = $("div#manager").attr("data-match-id");

                doPost($("table#match-table").attr("data-response-url"), data, "POST", "json", options.callback, options.beforeSend);

                $("div.explode").css("display", "block");

                $("p.var-carrier").dblclick(function (e) {
                    e.stopImmediatePropagation();
                    var value = $(this).text() || "";
                    if (value == "---") {
                        value = 0;
                    }
                    $(this).text("");
                    $(this).append('<input type="text" class="inputData" value="' + value + '">');
                    $("input.inputData").focus();
                    $("input.inputData").keypress(function (e) {
                        if (e.which == 13) {
                            $(this).focusout();
                        }
                    });
                    $("input.inputData").focusout(function (e) {
                        e.stopPropagation();
                        var val = $(this).val() || "---";
                        $(this).parent("p.var-carrier").text(val);
                        //$(this).remove();
                        var data = {},
                            options = {
                            callback: function (e) {
                                $(".loader").fadeOut(500);
                            },
                            beforeSend: function () {
                                $(".loader").fadeIn(500);
                            }
                        };

                        console.log($(this));
                        data["rowID"] = $("div#manager").attr("data-match-id");
                        data["newValue"] = $(this).val() || 0;
                        data["column"] = color + num + $(this).parent("p.var-carrier").prop("class").split("-")[1] + $(this).parent("p.var-carrier").prop("class").split("-")[2];
                        data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
                        doPost($("div#manager").attr("data-set-action"), data, "POST", "json", options.callback, options.beforeSend);
                    });
                });
            }
        });
    $("span.dblclick").dblclick(function (e) {
        e.stopImmediatePropagation();
        var value = $(this).text() || "";
        if (value == "---") {
            value = 0;
        }
        $(this).text("");
        $(this).append('<input type="text" class="inputData" value="' + value + '">');
        $("input.inputData").focus();
        $("input.inputData").keypress(function (e) {
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
                $("span.dblclick").off("dblclick");
                    doSet(e);
                    doDBLClick(e);
                    if (detectMobile()) {
                        $("nav.navbar").addClass("nav-close");
                        $("body#body-frame").addClass("nav-body-close");
                        hideTable();
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
        clickHandler(e);
    });
}
$(document).ready(function () {
    "use strict";
    getTable();
});
