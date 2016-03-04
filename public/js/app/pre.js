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
                $("input#teamnames").val(e.teamnames.capitalize());
                $("input#teamnumbers").val(x);
                $("input#website").val(e.webaddress);
                $("input#email").val(e.email);
                $("input#city").val(e.city);
                $("input#state").val(e.state);
                $("div.new-team > h3:first").text("Edit Team");
            }
        );
    });
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
                    $(".b_type").hide();
                    triggerOnClick();
                });
            }, 500);
        },
        function () {
            $("div.container-main").prepend('<img class="response-img" src="' + $("h1.main-header").attr("data-response-url") + '" height="50px" width="50px" data-success-url="' + $("h1.main-header").attr("data-success-url") + '">');
            $("h1.main-header").remove();
            $("p.lead").text("Retriving the data...");
        }
    );
}

$(document).ready(function () {
    getTable();
    $("form#new-team-form")[0].reset();
    $("input[name='teamnumbers']").on("input", function () {
        $(this).parent("div.form-group").removeClass("has-error has-feedback");
        $(this).tooltip("hide");
    });
    $("form#new-team-form").submit(function (e) {
        e.preventDefault();

        if ($("input[name='teamnumbers']").val() <= 0) {
            $("input[name='teamnumbers']").parent("div.form-group").addClass("has-error has-feedback");
            $("input[name='teamnumbers']").tooltip({
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
                    $("div.new-team > h3:first").text("Scout New Team");
                    doPost(
                        $("div#body-holder").attr("data-table-url"),
                        null,
                        "GET",
                        "html",
                        function (e) {
                            $("div#al-teams").html(e);
                            $(".b_type").hide();
                            triggerOnClick();
                        }
                    );
                }
            }
        );
    });
});