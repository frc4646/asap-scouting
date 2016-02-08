/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP)
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2016 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     v1-beta
 */

if("undefined"==typeof jQuery)throw new Error("form.js requires jQuery");
+function(a){"use strict";var b=a.fn.jquery.split(" ")[0].split(".");if(b[0]<2&&b[1]<9||1==b[0]&&9==b[1]&&b[2]<1)throw new Error("form.js requires jQuery version 1.9.1 or higher")}(jQuery)

function doPost(action, data, type, dataType, callback, beforeSend) {
    "use strict";

    $.ajax({
        url: action,
        type: type,
        dataType: dataType,
        beforeSend: beforeSend,
        data: data,
        success: callback
    });
}

function showPWDIndicator(id, visibility) {
    "use strict";
    switch (visibility) {
        case "show":
            $("#" + id).popover({
                html: true,
                title: "Password Strenght",
                content: '<p>Password Strenght</p> <div class="progress"><div id="password-score" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%"></div></div>'
            }, "show");
            break;
        case "destroy":
            $("#" + id).popover("destroy");
            break;
        case "hide":
            $("#" + id).popover("hide");
            break;
        default:
            $("#" + id).popover("destroy");
            break;
    }

}
$(document).ready(function () {
    $("input[name=\"password\"].metre").focusin(function (e) {
        console.info("focusIn");
        e = e.target;
        showPWDIndicator(e.id, "show");
    });

    $("input[name=\"password\"].metre").focusout(function (e) {
        console.info("focusOut");
        e = e.target;
        $("#" + e.id).popover("destroy");
        //showPWDIndicator(e.id, "destroy");
    });
    /*$("input[name="password"].metre").blur(function (e) {
        showPWDIndicator(e.target.id, "destroy");
    }).focusout(function (e) {
        showPWDIndicator(e.target.id, "destroy");
    }).focusin(function (e) {
        showPWDIndicator(e.target.id, "show");
    }).focus(function (e) {
        showPWDIndicator(e.target.id, "show");
    });*/
    $("input[name=\"password\"].metre").keyup(function (e) {
        e = e.target;
        console.log(zxcvbn(e.value));

        var score = zxcvbn(e.value).score;

        console.log(score);

        $("#password-score").alterClass("progress-bar-*");
        $("#password-score").prop("style", "width:" + score * 25 + "%").text(score * 25 + "%").attr("aria-valuenow", score * 25);

        switch (score) {
            case 1:
                $("#password-score").addClass("progress-bar-danger");
                break;
            case 2:
                $("#password-score").addClass("progress-bar-warning");
                break;
            case 3:
                $("#password-score").addClass("progress-bar-warning");
                break;
            case 4:
                $("#password-score").addClass("progress-bar-success");
                break;
            default:
                $("#password-score").addClass("progress-bar-info");
                break;
        }
    });

    $("form.doPost").submit(function (e) {
        e.preventDefault();

        console.info(e);

        var defaults = {
                action: $(this).attr("action"),
                data: false,
                type: "POST",
                dataType: "text",
                beforeSend: null,
                callback: function (e) {
                    if (e.success) {
                        $(location).attr("href", e.url);
                    } else {
                        console.warn(e);
                    }
                }
            };

        var override = {}, sendOut = {};
        $(this).find("input, textarea").each(function (e) {
            console.info(this);
            if (this.type == "checkbox") {
                sendOut[this.name] = $(this).prop("checked");
            } else {
                sendOut[this.name] = this.value;
            }
        });
        console.log(sendOut);

        if ($.isFunction(window.onLoad) === "function") {
            override = onLoad();
        }

        if (!$.isEmptyObject(sendOut)) {
            defaults.data = sendOut;
        } else {
            throw new Error("No fields found!");
        }

        if ($.isFunction(override.callback)) {
            defaults.callback = override.callback;
        }

        if ($.isFunction(override.beforeSend)) {
            defaults.beforeSend = override.beforeSend;
        }

        if (typeof override.returnType !== "undefined") {
            switch (override.returnType) {
                case "url":
                    defaults.dataType = "text";
                    break;
                case "html":
                    defaults.dataType = "html";
                    break;
                default:
                    defaults.dataType = "json";
                    break;
            }
        }

        console.info(defaults);

        doPost(defaults.action, defaults.data, defaults.type, defaults.dataType, defaults.callback, defaults.beforeSend);
    });
});
