<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\NoTableException;
use App\Models\Teams;

class Matches extends Model
{
    protected $table = "matches_2017ilpe";

    protected $prefix = "matches_";

    protected $fillable = [
        "match_id",
        "time",
        "bluescore",
        "redscore",
        "red1",
        "red2",
        "red3",
        "blue1",
        "blue2",
        "blue3",
    ];

    public static $teamColumns = [
        "red1",
        "red2",
        "red3",
        "blue1",
        "blue2",
        "blue3",
    ];

    public function __construct($regional = null) {
        parent::__construct();
        $regional = $regional ?: $_SESSION["regional"];
    	if (is_string($regional)) {
    		$this->setTable($this->prefix . $regional);
    	}
    }

    public function getPrefix()
    {
    	return $this->prefix;
    }

    public function teams()
    {
        $result = [];

        foreach($this::$teamColumns as $column) {
            $result[] = $this->hasMany(Teams::class, "number", $column)->first();
        }

        return new Collection($result);
    }
}
