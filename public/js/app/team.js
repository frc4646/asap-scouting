if (!String.prototype.capitalize) {
    String.prototype.capitalize = function () {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };
};

var defenceChart = null;
var defenceChartTeam4Tele = null;
var defenceChartTeam3Tele = null;
var defenceChartTeam2Tele = null;
var defenceChartTeam1Tele = null;
var defenceChartTeam4Auto = null;
var defenceChartTeam3Auto = null;
var defenceChartTeam2Auto = null;
var defenceChartTeam1Auto = null;
var toggled = false;

function makeRandomColor() {
    var color = "#";
    for (k = 0; k < 3; k++) {
        color += ("0" + (Math.random()*256|0).toString(16)).substr(-2);
    }
    return color;
}

function insertButtons(e) {
    //console.log(e);
    var x = $("button.team-select:first").clone();
    $("button.team-select").remove();

    $.each(e, function () {
        var z = x.clone();
        //console.log(z);
        //console.log(this);
        $("ul[style='list-style: none;']").append(
            $("<li></li>").addClass("select").append(
                z.attr("data-team", this.team_id).removeClass("selected").text("Team " + this.team_id + " | " + this.details.name)
            )
        );
    });
    $("li.select:not(:has( > button))").remove();
    bindClick();
}

function genCharts(e) {
    console.log(e);
    if (!$("div#charts").is(":hidden")) {
        if (defenceChart != null) {
            defenceChart.destroy();
        }
        var defenceData = [];
        $.each(e, function (ez) {
            var k = makeRandomColor(),
                title = ez.split("_");

            $.each(title, function (t) {
                title[t] = title[t].capitalize();
            });

            //console.log(title);

            title = title.join(" ");

            //console.log(title);

            defenceData.push({
                value: e[ez],
                color: k,
                hightlight: warna.lighten(k, 0.4).hex,
                label: title,
            });
        });
        var defence2d = $("canvas#primary-defence")[0].getContext("2d");
        defenceChart = new Chart(defence2d).Pie(defenceData, {
            animateScale: true,
            animationEasing: "linear",
            animationSteps: 20,
        });
    }   
}

function bindClick() {
    $("button.team-select").off("click");
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
                //genCharts(e);
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
                        //console.log(this);
                        //console.log(e);
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

                        var total = 0;
                        for(q = 0; q <= 2; q++) {
                            var char = String.fromCharCode(97+q);
                            //console.log(char);
                            if (!Number.isNaN(parseInt(this[0][this[1] + "score" + char]))) {
                                total += parseInt(this[0][this[1] + "score" + char]);
                            }

                            //console.log(parseInt(this[0][this[1] + "score" + char]));
                        }
                        //console.log(total);
                        ///console.log(this[0]);
                        //console.log(this[1]);
                        container.children("td.teamscore").text(total);

                        container.children("td." + this[1]).css({"text-decoration": "underline", "font-weight": "bold"});

                        $("table#matches-played > tbody").append(container);
                        
                    });

                    $("span#avg_score").text(e.scores.avg);
                    $("span#min_score").text(e.scores.min);
                    $("span#max_score").text(e.scores.max);
                }
            }
        );
        doPost(
            $("div#body-holder").attr("data-defence-url").split(":team").join($(this).attr("data-team")),
            null,
            "GET",
            "json",
            function (e) {
                genCharts(e);
            }
        );
    });
}

$(document).ready(function () {
	$("select.filter-select").change(function () {
		doPost(
			$("div#search-options").attr("data-search") + "?" + $.param(
                {
                    sort: $("select#select-filter").children("option:selected").val(),
                    dir: $("select#select-filter-dir").children("option:selected").val()
                }
            ),
			null,
			"GET",
			"json",
			function (e) {
                insertButtons(e);
			}
		);
	});
    $("button#compare").click(function () {
        if (!toggled) {
            $("div#team-info-hold").hide();
            $("div#team-compare").show();
            $(this).text("Exit Compare");
            toggled = true;
        } else {
            $("div#team-compare").hide();
            $("div#team-info-hold").show();
            $(this).text("Compare Teams");
            toggled = false;
        }
    });
    $("select.compare-teams-select").change(function () {
        var self = $(this);
        doPost(
            $("div#body-holder").attr("data-defence-url").split(":team").join(self.children("option:selected").val()) + "?" + $.param({
                type: "tele"
            }),
            null,
            "GET",
            "json",
            function (e) {
                if (!self.next("div.team-graph-holder").is(":hidden")) {
                    var tchart = "defenceChartTeam" + self.attr("id").split("-")[2] + "Tele";
                    if (window[tchart] != null) {
                        window[tchart].destroy();
                    }
                    var teamData = [];
                    $.each(e, function (ez) {
                        var k = makeRandomColor(),
                            title = ez.split("_");

                        $.each(title, function (t) {
                            title[t] = title[t].capitalize();
                        });

                        title = title.join(" ");

                        teamData.push({
                            value: e[ez],
                            color: k,
                            hightlight: warna.lighten(k, 0.4).hex,
                            label: title,
                        });
                    });
                    //self.next("div.team-graph-holder").children("div.tele-legend").empty();
                    var team2d = self.next("div.team-graph-holder").children("canvas.tele")[0].getContext("2d");
                    window[tchart] = new Chart(team2d).Pie(teamData, {
                        animateScale: true,
                        animationEasing: "linear",
                        animationSteps: 20,
                    });
                    //self.next("div.team-graph-holder").children("div.tele-legend").append(window[tchart].generateLegend());
                }  
            }
        );
        doPost(
            $("div#body-holder").attr("data-defence-url").split(":team").join(self.children("option:selected").val()) + "?" +$.param({
                type: "auto"
            }),
            null,
            "GET",
            "json",
            function (e) {
                if (!self.next("div.team-graph-holder").is(":hidden")) {
                    var tchart = "defenceChartTeam" + self.attr("id").split("-")[2] + "Auto";
                    if (window[tchart] != null) {
                        window[tchart].destroy();
                    }
                    var teamData = [];
                    $.each(e, function (ez) {
                        var k = makeRandomColor(),
                            title = ez.split("_");

                        $.each(title, function (t) {
                            title[t] = title[t].capitalize();
                        });

                        title = title.join(" ");

                        teamData.push({
                            value: e[ez],
                            color: k,
                            hightlight: warna.lighten(k, 0.4).hex,
                            label: title,
                        });
                    });
                    //self.next("div.team-graph-holder").children("div.auto-legend").empty();
                    var team2d = self.next("div.team-graph-holder").children("canvas.auto")[0].getContext("2d");
                    window[tchart] = new Chart(team2d).Pie(teamData, {
                        animateScale: true,
                        animationEasing: "linear",
                        animationSteps: 20,
                    });
                    //self.next("div.team-graph-holder").children("div.auto-legend").append(window[tchart].generateLegend());
                }  
            }
        );
    });
    bindClick();
});
