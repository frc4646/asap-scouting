<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Matches;

class Teams extends Model
{
	protected $table = "teams";

	protected $hidden = [
		"id",
		"created_at",
		"updated_at",
	];

	protected $fillable = [
		"number",
		"name",
		"auto_reach",
		"auto_cross",
		"auto_high",
		"auto_low",
		"tele_high",
		"tele_low",
		"tele_portcullis",
		"tele_cheval_de_frise",
		"tele_moat",
		"tele_ramparts",
		"tele_drawbridge",
		"tele_sally_port",
		"tele_rock_wall",
		"tele_rough_terrain",
		"tele_low_bar",
		"tele_scale",
		"tele_challenge",
	];

	public $mappings = [
		"auto_reach" => "Autonomous Reach",
		"auto_cross" => "Autonomous Cross",
		"auto_high" => "Autonomous High Goal",
		"auto_low" => "Autonomous Low Goal",
		"tele_high" => "TeleOP High Goal",
		"tele_low" => "TeleOP Low Goal",
		"tele_portcullis" => "TeleOP Portcullis",
		"tele_cheval_de_frise" => "TeleOP Cheval de Frise",
		"tele_moat" => "TeleOP Moat",
		"tele_ramparts" => "TeleOP Ramparts",
		"tele_drawbridge" => "TeleOP Drawbridge",
		"tele_sally_port" => "TeleOP Sally Port",
		"tele_rock_wall" => "TeleOP Rock Wall",
		"tele_rough_terrain" => "TeleOP Rough Terrain",
		"tele_low_bar" => "TeleOP Low Bar",
		"tele_scale" => "TeleOP Scale Tower",
		"tele_challenge" => "TeleOP Challenge Tower",
	];

	public function matches()
	{
		$results = [];

        foreach(Matches::$teamColumns as $column) {
        	$result = $this->belongsTo(Matches::class, "number", $column, Matches::class)->get();
        	if (!is_null($result)) {
        		$results[] = $result;
        	}
        }

        return (new Collection($results))->flatten();
	}

	public function getNameFor($column)
	{
		return $this->mappings[$column];
	}
}