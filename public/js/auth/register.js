function onLoad() {
    'use strict';
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
