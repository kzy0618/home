<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/17/18
 * Time: 3:37 PM
 */

namespace OCA\Home\Db;


class RecordingGeoDTO
{

    public $id;
    public $content;
    public $datetime;
    public $standaloneLon;
    public $standaloneLat;
    public $cityName;
    public $cityLon;
    public $cityLat;
    public $suburbName;
    public $suburbLon;
    public $suburbLat;
    public $isStandalone;
    public $isRepresentative;

}