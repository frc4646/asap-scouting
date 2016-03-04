if (!Array.prototype.last) {
    Array.prototype.last = function () {
        return this[this.length - 1];
    };
};

if (!String.prototype.capitalize) {
    String.prototype.capitalize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
};

function triggerOnClick() {
    $("button.deletable").off("click");
    $(".clickable").off("click")
    $("button.deletable").click(function (e) {
        e.stopPropagation();
        var team = $(this).parent("td").parent("tr.clickable").attr("data-team-id"),
            x = confirm("Are you sure you want to delete Team " + team);
        console.log(x);

        if (x) {
            doPost(
                $("div#body-holder").attr("data-team-delete").split(":team").join(team),
                null,
                "GET",
                "json",
                function (e) {
                    console.log(e);
                    if (e.success) {
                        doPost(
                            $("div#body-holder").attr("data-table-url"),
                            null,
                            "GET",
                            "html",
                            function (e) {
                                $("div#al-teams").html(e);
                                triggerOnClick();
                            }
                        );
                    }
                }
            );
        }
    });
    $(".clickable").click(function (e) {
        e.stopPropagation();
        var x = $(this).closest("tr[data-type=\"parent\"]").attr("data-team-id");
        doPost(
            $("div#body-holder").attr("data-get-url").split(":team").join(x),
            null,
            "GET",
            "json",
            function (e) {
                $("ul#team-comments > li").remove();
                $("div#photo-display > img").remove();
                $("div.new-team > h3:first").text("Edit Team");
                $("input#team-name").val(e.name.capitalize());
                $("input#team-num").val(x);
                $("input[name='nonce']").val(e.nonce);
                $("select[name='bot_type']").children("option#" + e.bot_type).attr("selected", "selected");
                $.each(e.comments, function (f) {
                    var z = $("input#target").clone();
                    z.css("display", "none");
                    z.addClass("targeted");
                    z.attr("hidden", "hidden");
                    z.attr("data-value", e.comments[f]);
                    z.removeAttr("placeholder", "");
                    z.removeAttr("id", "");
                    z.val(e.comments[f]);
                    $("input#target").parent("div.form-group").append(z);
                    $("ul#team-comments").append("<li>" + htmlEncode(e.comments[f]) + "<span class=\"glyphicon glyphicon-remove pull-right comment-remove\"></span></li>");
                });
                $.each(e.images, function (z) {
                    var fz = $("<img/>"),
                        id = e.images[z].split("/").last().split(".")[0];

                    if ($("img[data-photo-id=\"" + id + "\"]").length == 0) {
                        fz.addClass("img-responsive col-lg-3 col-xs-3 col-sm-3");
                        fz.attr("src", e.images[z]);
                        fz.attr("data-photo-id", id);
                        fz.appendTo("div#photo-display");
                    }
                });
                $("span.glyphicon-remove.comment-remove").click(function () {
                    //console.log($(this).parent().text());
                    var target = $(this).parent().text();

                    $("input[data-value='" + target + "'").remove();
                    $(this).parent("li").remove();
                });
            }
        );
    });
}
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
function hitTeamUrl(team) {
    doPost(
        $("div#body-holder").attr("data-call-url").split(":team").join(team),
        null,
        "GET",
        "json"
    );
}
function htmlEncode(value) {
    return $('<div/>').text(value).html();
}
function getTable() {
    doPost(
        $("div#body-holder").attr("data-table-url"),
        null,
        "GET",
        "html",
        function (e) {
            $("img.response-img").prop("src", $("img.response-img").attr("data-success-url"));
            setTimeout(function () {
                $(".container-main").fadeOut("slow", function () {
                    $(".container-main").remove();
                    $("div#body-holder").prepend(e);
                    triggerOnClick();
                });
            }, 500);
        },
        function () {
            $("div.container-main").prepend('<img class="response-img" src="' + $("h1.main-header").attr("data-response-url") + '" height="50px" width="50px" data-success-url="' + $("h1.main-header").attr("data-success-url") + '">');
            $("h1.main-header").remove();
            $("p.lead").text("Loading the latest...");
        }
    );
}

$(document).ready(function () {
    getTable();
    $("button#clear").click(function () {
        $("input.targeted").remove();
        $("ul#team-comments > li").remove();
        $("input[name='nonce']").val("");
        $("div.new-team > h3:first").text("Scout New Team");
    });
    $("input#target").keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();
            $("button.add-comment").click();
            $("span.glyphicon-remove.comment-remove").click(function () {
                //console.log($(this).parent().text());
                var target = $(this).parent().text();

                $("input[data-value='" + target + "'").remove();
                $(this).parent("li").remove();
            });
            return false;
        }
    });
    $("button.add-comment").click(function () {
        if ($("input#target").val() !== "") {
            var x = $("input#target").clone();
            x.css("display", "none");
            x.addClass("targeted");
            x.removeAttr("id");
            x.attr("hidden", "hidden");
            x.removeAttr("placeholder", "");
            $(this).parent("div.form-group").append(x);
            $("ul#team-comments").append("<li>" + htmlEncode($(this).siblings("input#target").val()) + "<span class=\"glyphicon glyphicon-remove pull-right comment-remove\"></span></li>");
            $(this).siblings("input#target").val("");
        }
    });
    $("input[name=\"team-num\"]").on("input", function () {
        $(this).parent("div.form-group").removeClass("has-error has-feedback");
        $(this).tooltip("hide");
    });
    $("button#photo-button").click(function (e) {
        if ($("input[name=\"team-num\"]").val() == "") {
            $("input[name=\"team-num\"]").parent("div.form-group").addClass("has-error has-feedback");
            $("input[name=\"team-num\"]").tooltip({
                placement: "right",
                trigger: "manual",
                title: "Team number is required!"
            }).tooltip("show");
            return;
        } else {
            hitTeamUrl($("input[name=\"team-num\"]").val());
            $("input#team_id").val($("input[name=\"team-num\"]").val());
        }
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

        $("#drop a").click(function () {
            $(this).parent().find("input").click();
        });

        $("form#upload").fileupload({
            dropZone: $("#drop"),
            add: function (e, data) {
                var tpl = $("<li class=\"working file-list\"><input type=\"text\" value=\"0\" data-width=\"48\" data-height=\"48\" data-fgColor=\"#0788a5\" data-readOnly=\"1\" data-bgColor=\"#3e4043\" /><p></p><span></span></li>");
                tpl.find("p").text(data.files[0].name)
                             .append("<i>" + formatFileSize(data.files[0].size) + "</i>");
                data.context = tpl.appendTo($("#upload ul"));
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

            progress: function(e, data) {
                
                var progress = parseInt(data.loaded / data.total * 100, 10);
                data.context.find("input").val(progress).change();
                if(progress == 100) {
                    data.context.removeClass("working");
                }
            },
            success: function (e, data) {
                console.log({e: e, data: data});

                $.each(e.images, function (z) {
                    var fz = $("<img/>"),
                        id = e.images[z].split("/").last().split(".")[0];

                    if ($("img[data-photo-id=\"" + id + "\"]").length == 0) {
                        fz.addClass("img-responsive col-lg-3 col-xs-3 col-sm-3");
                        fz.attr("src", e.images[z]);
                        fz.attr("data-photo-id", id);
                        fz.appendTo("div#photo-display");
                    }
                });
            },
            fail: function(e, data) {
                data.context.addClass("error");
            }

        });
        $(document).on("drop dragover", function (e) {
            e.preventDefault();
        });
    });
    $("form#new-team-form").submit(function (e) {
        e.preventDefault();
        
        $("input#target").attr("disabled", "disabled");

        if ($("input[name='team-num']").val() <= 0) {
            $("input[name='team-num']").parent("div.form-group").addClass("has-error has-feedback");
            $("input[name='team-num']").tooltip({
                placement: "right",
                trigger: "manual",
                title: "Invalid Team Number"
            }).tooltip("show");
            return false;
        }

        doPost(
            $(this).attr("action"),
            $(this).serialize(),
            "POST",
            "json",
            function (e) {
                if (e.error) {
                    $.each(e.error, function (v) {
                        $("input[name=\"" + e.error[v].split(" ")[0] + "\"]").parent("div.form-group").addClass("has-error has-feedback");
                    });
                } else {
                    $("form#new-team-form")[0].reset();
                    $("input.targeted").remove();
                    $("ul#team-comments > li").remove();
                    $("div.new-team > h3:first").text("Scout New Team");
                    doPost(
                        $("div#body-holder").attr("data-table-url"),
                        null,
                        "GET",
                        "html",
                        function (e) {
                            $("div#al-teams").html(e);
                            triggerOnClick();
                        }
                    );
                }
            },
            function () {
                $("input#target").removeAttr("disabled");
            }
        );
    });
});