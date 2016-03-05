/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP)
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2016 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     v1-beta
 */

if (!Array.prototype.last){
    Array.prototype.last = function(){
        return this[this.length - 1];
    };
};

function detectMobile() {
    "use strict";
    var uagent = navigator.userAgent.toLowerCase();

    if (uagent.search("mobile") > -1) {
        return true;
    } else {
        return false;
    }
}

function calculateTopScore(color) {
    var score = 0;
    $.each($("div[id|='score-" + color + "'] > span"), function () {
        if (!Number.isNaN(parseInt($(this).text()))) {
            score += parseInt($(this).text());
        }
    });

    if (!Number.isNaN(parseInt($("div#match-" + color + " > span").attr("aria-extra-score")))) {
        score += parseInt($("div#match-" + color + " > span").attr("aria-extra-score"));
    }
    
    $("div#match-" + color + "").children("span").text(score);

    var data = {
            rowID: $("div#manager").attr("data-match-id"),
            column: color + "score",
            newValue: score
        };
    data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
    $.ajax({
        url: $("div#manager").attr("data-set-action"),
        data: data,
        type: "POST",
        success: function () {
            $(".loader").fadeOut(500);
        },
        beforeSend: function () {
            $(".loader").fadeIn(500);
        }
    });
    data["newValue"] = $("div#match-" + color + " > span").attr("aria-extra-score");
    data["column"] = color + "extra";
    $.ajax({
        url: $("div#manager").attr("data-set-action"),
        data: data,
        type: "POST",
        success: function () {
            $(".loader").fadeOut(500);
        },
        beforeSend: function () {
            $(".loader").fadeIn(500);
        }
    });
}

function getTable() {
    "use strict";
    var options = {
        callback: function (e) {
            $("img.response-img").prop("src", $("img.response-img").attr("data-success-url"));
            $("button.btn-sc").click(function () {
                var score,
                    $is = $(this).parent("div.column"),
                    data = {
                        rowID: $("div#manager").attr("data-match-id"),
                        column: $is.prop("id").split("-")[1] + $is.prop("id").split("-")[3] + $is.prop("id").split("-")[0] + $is.prop("id").split("-")[2]
                    },
                    color;
                if (!Number.isNaN(parseInt($(this).siblings("span.dblclick").text()))) {
                    score = parseInt($(this).siblings("span.dblclick").text());
                } else {
                    score = 0;
                }
                
                if ($(this).hasClass("btn-inc")) {
                    score++;
                } else if ($(this).hasClass("btn-dec")) {
                    score--;
                }
                $(this).siblings("span.dblclick").text(score);
                data["newValue"] = score.toString();
                data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
                calculateTopScore($(this).parent("div").prop("id").split("-")[1])
                $.ajax({
                    url: $("div#manager").attr("data-set-action"),
                    data: data,
                    type: "POST",
                    success: function () {
                        $(".loader").fadeOut(500);
                    },
                    beforeSend: function () {
                        $(".loader").fadeIn(500);
                    }
                });
            });
            $("button.match-buttons").click(function () {
                var amnt = parseInt($(this).attr("aria-point-val")),
                    team = $(this).prop("id").split("-")[0],
                    self = $(this);
                
                if ($(this).prop("id").split("-")[1] == "foul") {
                    switch (team) {
                        case "red":
                            team = "blue";
                            break;
                        case "blue":
                            team = "red";
                            break;
                    }
                }
                
                if ($("div#manager").is(":visible")) {
                    if (!Number.isNaN(parseInt($("div#match-" + team + " > span").attr("aria-extra-score")))) {
                        amnt += parseInt($("div#match-" + team + " > span").attr("aria-extra-score"));
                    }
                    
                    $("div#match-" + team + " > span").attr("aria-extra-score", amnt);
                    calculateTopScore(team);
                } else {
                    var $data = {
                            team_id: $("div#team-options").attr("data-team-id"),
                            thing: $(this).prop("id").split("-")[1],
                            match: $("div#manager").attr("data-match-id")
                        },
                        $action = $("div#team-options").attr("data-button-set-url");

                    $data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");

                    doPost(
                        $action,
                        $data,
                        "POST",
                        "json",
                        function (e) {
                            console.log(e);
                            self.text(self.attr("aria-display") + " " + e);
                        }
                    );
                }
            });
            $(".container-main").fadeOut("slow", function () {
                success(e);
            });
        },
        beforeSend: function () {
            $("div.container-main").prepend('<img class="response-img" src="' + $("h1.main-header").attr("data-response-url") + '" height="50px" width="50px" data-success-url="' + $("h1.main-header").attr("data-success-url") + '">');
            $("h1.main-header").remove();
            $("p.lead").text("Crunching the latest data...");
        }
    };
    doPost($("div#body-holder").attr("data-content-url"), null, "GET", "html", options.callback, options.beforeSend);
}

function success(e) {
    "use strict";
    $("div#body-container").removeClass("container");
    $("div#body-container").addClass("container-fluid");
    $(".container-main").remove();
    $("div#replaceDiv").append(e);
    $("select#filter").change(function () {
        switch($(this).children("option:selected").val()) {
            case "all":
                $("table#match-table > tbody > tr").show();
                break;
            case "up":
                $("table#match-table > tbody > tr").show();
                $("table#match-table > tbody > tr").each(function () {
                    if (!($(this).children("td#bluescore").text() === "") && !($(this).children("td#redscore").text() === "")) {
                        $(this).hide();
                    }
                });
                break;
            case "old":
                $("table#match-table > tbody > tr").show();
                $("table#match-table > tbody > tr").each(function () {
                    if (($(this).children("td#bluescore").text() === "") && ($(this).children("td#redscore").text() === "")) {
                        $(this).hide();
                    }
                });
                break;
        }
    });
    $("div#replaceDiv").fadeIn(500);
    if (!detectMobile()) { // Not Available on mobile
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

    $("div#match-red > span").attr("aria-extra-score", e["redextra"]);
    $("div#match-blue > span").attr("aria-extra-score", e["blueextra"]);
    
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
        $("div.team-num").click(function (e) {
            $("div#team-options").attr("data-team-id", $(this).attr("data-team-num"));
            e.stopImmediatePropagation();
            e.stopPropagation();
            var color = $(this).prop("id").split("-")[1],
                num = $(this).prop("id").split("-")[2];
            $("button.toggle-defense").off("click");
            $("button#view-photo").off("click");
            $("ul#file-list > li.file-list").each(function () {
                $(this).remove();
            });
            $("div#manager").hide();
            $("div#view-team-photos").attr("data-run", 0);
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
                                $.each($("ul#team-comments > li"), function () {
                                   $(this).remove(); 
                                });
                                $.each($("img.team-img"), function () {
                                    $(this).remove();
                                });
                                if (x.images.length == 0) {
                                    if ($("div#view-team-photos").children("h4#image-not-found").length == 0) {
                                        $("div#view-team-photos").append("<h4 id=\"image-not-found\">No Images Found</h4>");
                                    }
                                } else if (x.images.length > 0) {
                                    $.each(x.images, function (i) {
                                        var name = x.images[i].split(".")[0].split("/").last();
                                        if ($("img[data-photo-id=\"" + name + "\"]").length == 0) {
                                            $.ajax({
                                                url: x.images[i],
                                                type: "HEAD",
                                                success: function () {
                                                    if ($("div#view-team-photos").children("h4#image-not-found").length > 0) {
                                                        $("div#view-team-photos").children("h4#image-not-found").remove();
                                                    }
                                                    //$("").append($("div#view-team-photos > span.img-overlay").clone());
                                                    $("div#view-team-photos").append("<img class=\"img-responsive team-img\" src=\"" + x.images[i] + "\" data-photo-id=\"" + name + "\"/>");
                                                    //$("div#view-team-photos > span.img-overlay").removeClass("nodisplay");

                                                },
                                                error: function () {
                                                    if ($("div#view-team-photos").children("h4#image-not-found").length == 0) {
                                                        $("div#view-team-photos").append("<h4 id=\"image-not-found\">No Images Found</h4>");
                                                    }
                                                    /*if (!$("div#view-team-photos > span.img-overlay").hasClass("nodisplay")) {
                                                        $("div#view-team-photos > span.img-overlay").addClass("nodisplay");
                                                    }*/
                                                }
                                            });
                                        }
                                    });
                                }
                                /*if (!x.runOnce) {
                                    $("button.toggle-defense").each(function () {
                                        $(this).attr("data-count", 0);
                                        var tf = x["defences"][$(this).prop("id").split("-")[1]];
                                        if (tf == "true") {
                                            $(this).alterClass("btn-info btn-success btn-danger", "btn-success");
                                        } else if (tf == "false") {
                                            $(this).alterClass("btn-info btn-success btn-danger", "btn-danger");
                                        }
                                    });
                                } else {
                                    $("button.toggle-defense").each(function () {
                                        $(this).alterClass("btn-info btn-success btn-danger", "btn-info");
                                    });
                                }*/
                                if (x.hasOwnProperty("comments")) {
                                    $.each(x.comments, function (e) {
                                       $("ul#team-comments").append("<li>" + x.comments[e] + "</li>")
                                    });
                                }
                            }
                        );
                    }
                }
            );

            $("button.toggle-defense").click(function () {
                var self = $(this)
                dart["button_id"] = $(this).attr("id").split("-")[1];
                dart["team_id"] = number;
                dart["match_id"] = $("div#manager").attr("data-match-id");
                doPost(
                    $("div#body-holder").attr("data-button-url"),
                    dart,
                    "POST",
                    "json",
                    function (e) {
                        
                        self.text(self.attr("aria-display") + " " + e.value)
                        console.log(e);
                    }
                );
                /*dart["item"] = $(this).prop("id").split("-")[1]
                if ($(this).hasClass("btn-info")) {
                    dart["val"] = true
                    $(this).alterClass("btn-info btn-success btn-danger", "btn-success");
                } else if ($(this).hasClass("btn-success")) {
                    dart["val"] = false
                    $(this).alterClass("btn-info btn-success btn-danger", "btn-danger");
                } else if ($(this).hasClass("btn-danger")) {
                    dart["val"] = true
                    $(this).alterClass("btn-info btn-success btn-danger", "btn-success");
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
                                    $(this).alterClass("btn-info btn-success btn-danger", "btn-success");
                                } else if (tf == "false") {
                                    $(this).alterClass("btn-info btn-success btn-danger", "btn-danger");
                                }
                            });
                        } else {
                            $("button.toggle-defense").each(function () {
                                $(this).alterClass("btn-info btn-success btn-danger", "btn-info");
                            });
                        }
                    },
                    function () {
                        $(".loader").fadeIn(500);
                    }
                );*/
            });
            $("select[name='hprank']").change(function () {
                //console.log($(this).find("option:selected").val());
                var data = {};

                data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
                data["team_id"] = number;
                data["hprank"] = $(this).find("option:selected").val();

                doPost(
                    $(this).parents("form").attr("action"),
                    data,
                    "POST",
                    "json"
                );
            });
            $("form#comment-post").submit(function (e) {
                e.preventDefault();

                if ($(this).find("textarea[name=\"comment\"]").val() == "") {
                    return false;
                }

                var data = {},
                    self = $(this);

                data[$("span.csrf_token").attr("data-name")] = $("span.csrf_token").prop("id");
                data["team_id"] = number;
                data["comment"] = self.find("textarea[name=\"comment\"]").val();

                doPost(
                    $(this).attr("action"),
                    data,
                    "POST",
                    "json",
                    function (e) {
                        $(".loader").fadeOut(500);
                        //console.log(e);
                        $("ul#team-comments > li").remove();
                        $.each(e.comments, function (x) {
                            $("div.team-comments > ul#team-comments").append("<li id=\"" + x + "\">" + e.comments[x] + "</li>");
                            //console.log(x);
                        });
                        self.find("textarea[name=\"comment\"]").val("");
                    },
                    function () {
                        $(".loader").fadeIn(500);
                    }
                );
            });

            $("button#view-photo").click(function () {
                $("div#photo-gallery").removeClass("nodisplay");
                var y = new Date();
                doPost(
                    $("div#photo-gallery").attr("data-blue-photos").split(":team").join(number).split(":year").join(y.getFullYear()),
                    null,
                    "GET",
                    "json",
                    function (e) {
                        if (!$.isEmptyObject(e.base_uri)) {
                            if ($("div#view-blue-photos").children("h4#image-not-found").length > 0) {
                                $("div#view-blue-photos").children("h4#image-not-found").remove();
                                return;
                            }
                            if ("cdphotothread" in e) {
                                $.each(e.cdphotothread, function (v) {
                                    var name = e.cdphotothread[v];
                                    if ($("div#view-blue-photos").children("img[src=\"" + e.base_uri.cdphotothread + name + "\"]").lenght <= 0) {
                                        $("div#view-blue-photos").append("<img class=\"img-responsive\" src=\"" + name + "\"/>");
                                    }
                                });
                                return;
                            } else {
                                $("div#view-blue-photos").children("h4#image-not-found").remove();
                                return;
                            }
                        } else if ($("div#view-blue-photos").children("h4#image-not-found").length <= 0) {
                            $("div#view-blue-photos").append("<h4 id=\"image-not-found\">No Images Found</h4>");
                        }
                    }
                );
                if ($("div#view-team-photos").attr("data-run") == 1) {
                    doPost(
                        $("div.team-details").attr("data-get-url").split(":team").join(number),
                        null,
                        "GET",
                        "json",
                        function (x) {
                            if (x.images.length == 0) {
                                if ($("div#view-team-photos").children("h4#image-not-found").length == 0) {
                                    $("div#view-team-photos").append("<h4 id=\"image-not-found\">No Images Found</h4>");
                                }
                            } else if (x.images.length > 0) {
                                $("img.team-img").remove();
                                $.each(x.images, function (i) {
                                    var name = x.images[i].split(".")[0].split("/").last();
                                    if ($("img[data-photo-id=\"" + name + "\"].team-img").length == 0) {
                                        $.ajax({
                                            url: x.images[i],
                                            type: "HEAD",
                                            success: function () {
                                                if ($("div#view-team-photos").children("h4#image-not-found").length > 0) {
                                                    $("div#view-team-photos").children("h4#image-not-found").remove();
                                                }
                                                //$("").append($("div#view-team-photos > span.img-overlay").clone());
                                                $("div#view-team-photos").append("<img class=\"img-responsive team-img\" src=\"" + x.images[i] + "\" data-photo-id=\"" + name + "\"/>");
                                                //$("div#view-team-photos > span.img-overlay").removeClass("nodisplay");

                                            },
                                            error: function () {
                                                if ($("div#view-team-photos").children("h4#image-not-found").length == 0) {
                                                    $("div#view-team-photos").append("<h4 id=\"image-not-found\">No Images Found</h4>");
                                                }
                                                /*if (!$("div#view-team-photos > span.img-overlay").hasClass("nodisplay")) {
                                                    $("div#view-team-photos > span.img-overlay").addClass("nodisplay");
                                                }*/
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    );
                }
                $("div#view-team-photos").attr("data-run", 1);
            });
            $("button#hide-photo").click(function () {
                $("div#photo-gallery").addClass("nodisplay");
            });
            $("button#photo-button").click(function (e) {
                $(".modal").modal();
                $("a#photo-clear").click(function () {
                    $("ul#file-list > li.file-list").each(function () {
                        $(this).remove();
                    });
                });
                $("#upload").on("shown.bs.modal", function () {
                    $("#upload").removeAttr("style");
                });
                e.preventDefault();
                $("input#team_id").val(number);
                var ul = $("#upload ul");
                $("#drop a").click(function () {
                    $(this).parent().find("input").click();
                });
                $("form#upload").fileupload({
                    dropZone: $("#drop"),
                    add: function (e, data) {
                        var tpl = $("<li class=\"working file-list\"><input type=\"text\" value=\"0\" data-width=\"48\" data-height=\"48\" data-fgColor=\"#0788a5\" data-readOnly=\"1\" data-bgColor=\"#3e4043\" /><p></p><span></span></li>");
                        tpl.find("p").text(data.files[0].name)
                                     .append("<i>" + formatFileSize(data.files[0].size) + "</i>");
                        data.context = tpl.appendTo(ul);
                        tpl.find("input").knob();
                        tpl.find("span").click(function () {
                            if(tpl.hasClass("working")) {
                                jqXHR.abort();
                            }
                            tpl.fadeOut(function () {
                                tpl.remove();
                            });

                        });
                        var jqXHR = data.submit();
                    },
                    progress: function(e, data){
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        data.context.find("input").val(progress).change();
                        if(progress == 100){
                            data.context.removeClass("working");
                        }
                    },
                    fail: function(e, data){
                        data.context.addClass("error");
                    }
                });
                $(document).on("drop dragover", function (e) {
                    e.preventDefault();
                });
                function formatFileSize(bytes) {
                    if (typeof bytes !== "number") {
                        return "";
                    }

                    if (bytes >= 1000000000) {
                        return (bytes / 1000000000).toFixed(2) + " GB";
                    }

                    if (bytes >= 1000000) {
                        return (bytes / 1000000).toFixed(2) + " MB";
                    }

                    return (bytes / 1000).toFixed(2) + " KB";
                }
            });
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
            calculateTopScore($is.prop("id").split("-")[1]);
            doPost($("div#manager").attr("data-set-action"), data, "POST", "json", options.callback, options.beforeSend);
        });
    });
}

function clickHandler(e, doOptions) {
    doOptions = doOptions || false;
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
                //$("nav.navbar").addClass("nav-close");
                //$("body#body-frame").addClass("nav-body-close");
                $("div#manager").removeAttr("style");
                $("div#manager").css("padding-left", "0px");
            }
            $("div#replaceDiv").fadeOut(500, function () {
                $(this).addClass("nodisplay");
                $("button.match-buttons").each(function () {
                    $(this).text($(this).attr("aria-display"));
                });
                $("div#manager").alterClass("col-lg-6", "col-lg-8").on("transitionend", function (e) {
                    $("button#reshow-table").off("click");
                    $("div#manager").off("transitionend");
                    //console.log($("div#match-buttons").attr("style"));
                    $("div#match-buttons").show().removeClass("nodisplay");
                    console.log($("div#match-buttons").attr("style"));
                    if (!detectMobile()) {
                        $("button.btn-sc").removeClass("nodisplay").fadeIn(300);
                    }
                    $("button#reshow-table").click(function () {
                        doPost(
                            $("div#body-holder").attr("data-content-url"),
                            null,
                            "GET",
                            "html",
                            function (e) {
                                $(".loader").fadeOut(500);
                                $("div#replaceDiv").children("div.table-holder").remove();
                                $("div#replaceDiv").append(e);
                                $("div#match-buttons").fadeOut(300).addClass("nodisplay");
                                if (!detectMobile()) {
                                    $("button.btn-sc").fadeOut(300).addClass("nodisplay");
                                }
                                $("div#manager").hide();
                                $("div#manager").alterClass("col-lg-8", "col-lg-6");
                                $("div#bottom-values").hide();
                                $("div#team-options").hide();
                                $("div#replaceDiv").alterClass("col-lg-6", "col-lg-12")
                                $("div#replaceDiv").show().removeClass("nodisplay");
                                if (!detectMobile()) {
                                    $("#match-table").tablesorter({
                                        theme: "bootstrap",
                                        headerTemplate: "{content} {icon}",
                                        widgets: ["filter"]
                                    });
                                }
                                doClick();
                            },
                            function () {
                                $(".loader").fadeIn(500);
                            }
                        );
                    });
                });
                if (detectMobile()) {
                    $("div#manager").trigger("transitionend");
                }
            }).addClass("nodisplay");
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
    $("div#replaceDiv").css("padding-right", "0px");
    $("tr.body-row").off("click");
    $("tr.body-row").click(function (e) {
        $("div#replaceDiv").alterClass("col-lg-12", "col-lg-6").on("transitionend", function () {
            $("div#manager").fadeIn(500);
        });
        $("div#manager").removeClass("nodisplay");
        $("div.bottom-values").removeClass("nodisplay");
        e.stopPropagation();
        e.stopImmediatePropagation();
        clickHandler(e);
    });
}

$(document).ready(function () {
    "use strict";
    
    //console.log($(window).width());
    //console.log($(window).height());
    
    if ($(window).width() <= 420 && $(window).height() <= 200) {
        //console.log("Show need bigger screen here");
        alert("You may see alignment issues due to the size of your screen. Please try again on a bigger screen for the best experience.")
    } else if ($(window).width() >= 420 && $(window).height() >= 200) {
        getTable();
    
        /*$("div#match-scores").off("action-score-change").on("action-score-change", function (e) {
            console.log(e);
            console.log($(this).parent(""));
        });*/
    }
});
