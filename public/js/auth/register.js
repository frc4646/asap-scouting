/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP)
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2016 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     v1-beta
 */

function onLoad() {
    "use strict";
    var override = {
        callback: function (e) {
            if (e.success !== true) {
                $(".form-signin").parent().add('<p style="color: red;"> Invalid Credentials </p>').appendTo($(".form-signin").parent());
                $(".form-control").each(function () {
                    $(this).parent().attr("class", "form-group has-error has-feedback");
                });
            } else {
                $(location).attr("href", e.url);
            }
        },
        returnType: "json"
    };

    return override;
}
