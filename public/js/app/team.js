if (!String.prototype.capitalize) {
    String.prototype.capitalize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
};

$(document).ready(function () {
	$("div#search").click(function () {
		doPost(
			"/data/sort",
			null,
			"GET",
			"json",
			function (e) {
				console.log(e);
			}
		);
	});
	$("button.team-select").click(function () {
		var self = $(this);
		$("button.team-select.selected").removeClass("selected btn-info");
		$(this).addClass("selected btn-info");

		doPost(
			$("div#body-holder").attr("data-get-url").split(":team").join($(this).attr("data-team")),
			null,
			"GET",
			"json",
			function (e) {
				$("h3.team-name").text("Team " + e.name + " | Team " + self.attr("data-team"));
				$("span#team-name").text("Team " + e.name);
				$("span#team-number").text("Team " + self.attr("data-team"));
				$("span#hprank").text(e.hprank.capitalize());
				$("span#bot_type").text(e.bot_type.capitalize());
				$("ul#team-comments > li").remove();
				$.each(e.comments, function (g) {
					$("ul#team-comments").append("<li>" + e.comments[g] + "</li>");
				});

				$("button.toggle-defense").each(function () {
                    var tf = e["defences"][$(this).prop("id").split("-")[1]];
                    if (tf == "true" || tf == true) {
                        $(this).alterClass("btn-info btn-danger", "btn-success");
                    } else if (tf == "false" || tf == false) {
                        $(this).alterClass("btn-info btn-success", "btn-danger");
                    }
                });

				$("div.team-info").removeClass("nodisplay");
			}
		);
		doPost(
			$("div#body-holder").attr("data-match-url").split(":team").join($(this).attr("data-team")),
			null,
			"GET",
			"json",
			function (e) {
				if (e) {
					$("table#matches-played > tbody > tr").not("tr#copy-id").remove();
					$.each(e.matches, function (v) {
						var container = $("tr#copy-id").clone(),
							match = this["match"],
							score = 0;
						container.removeClass("nodisplay");
						container.removeAttr("id");
						container.children("td.match").text(this[0]["match"]);
						container.children("td.red1").text(this[0]["red1"]);
						container.children("td.red2").text(this[0]["red2"]);
						container.children("td.red3").text(this[0]["red3"]);
						container.children("td.blue1").text(this[0]["blue1"]);
						container.children("td.blue2").text(this[0]["blue2"]);
						container.children("td.blue3").text(this[0]["blue3"]);
						container.children("td.redscore").text(this[0]["redscore"]);
						container.children("td.bluescore").text(this[0]["bluescore"]);

						container.children("td.teamscore").text(this[0][this[1]]);

						container.children("td." + this[1]).css({"text-decoration": "underline", "font-weight": "bold"});

						$("table#matches-played > tbody").append(container);
					});

					$("span#avg_score").text(e.scores.avg);
					$("span#min_score").text(e.scores.min);
					$("span#max_score").text(e.scores.max);
				}
			}
		);
	});
});