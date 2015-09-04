<?php
/**
 * Created by IntelliJ IDEA.
 * User: ivanlavoryk
 * Date: 9/3/15
 * Time: 4:20 PM
 */

include "vendor/autoload.php";
use Parse\ParseClient;
use Parse\ParseQuery;

class ResourcesBuilder {
    private $tasks = [];
    private $imageResources;

    private $resourcePath = "resource";
    private $taskPath = "assets/tasks";
    private $egretProjectPath = "../quiz2";

    function __construct() {
        $this->imageResources = (object) ["groups" => [], "resources" => []];
        $this->imageResources->groups[] = (object)["keys"=>"tasks", "name" => "preload_task"];
        $this->imageResources->resources[] = (object)["name" =>  "tasks", "type" => "json", "url" => $this->taskPath.DIRECTORY_SEPARATOR."task.json"];

        if( !is_dir($this->fullPath2Images())) {
            mkdir($this->fullPath2Images(), 0777, true);
        }

        $parseCfgStr = file_get_contents($this->egretProjectPath.DIRECTORY_SEPARATOR.$this->resourcePath.DIRECTORY_SEPARATOR."configs/php.json");
        $parseCfg = json_decode($parseCfgStr);
        ParseClient::initialize( $parseCfg->app_id, $parseCfg->rest_key, $parseCfg->master_key);

    }

    public function fetchParseData() {
        $query = new ParseQuery("Task");
        $callBack = Array($this, "callBack");
        $query->each($callBack);
    }

    public function callBack($obj) {
        $parseFile = $obj->get("file");
        copy($parseFile->getURL(), $this->fullPath2Images() . DIRECTORY_SEPARATOR . $parseFile->getName());
        $this->tasks[] = $obj->_encode();
        $this->imageResources->resources[] = (object)["name" =>  $parseFile->getName(), "type" => "image", "url" => $this->taskPath."/images/".$parseFile->getName()];
    }

    public function fullPath2Images() {
        return $this->egretProjectPath.DIRECTORY_SEPARATOR.$this->resourcePath.DIRECTORY_SEPARATOR.$this->taskPath.DIRECTORY_SEPARATOR."images";
    }

    public function fullPath2Task() {
        return $this->egretProjectPath.DIRECTORY_SEPARATOR.$this->resourcePath.DIRECTORY_SEPARATOR.$this->taskPath;
    }

    public function fullPath2Resources() {
        return $this->egretProjectPath.DIRECTORY_SEPARATOR.$this->resourcePath;
    }

    public function writeJSONs() {
        $taskJSONString = "{\"tasks\":[".implode( ",", $this->tasks)."]}";
        file_put_contents($this->fullPath2Task().DIRECTORY_SEPARATOR."task.json", $taskJSONString);
        file_put_contents($this->fullPath2Resources().DIRECTORY_SEPARATOR."resource_task.json", json_encode($this->imageResources, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}


date_default_timezone_set("Europe/Kiev");

$resourceBuilder = new ResourcesBuilder();
$resourceBuilder->fetchParseData();
$resourceBuilder->writeJSONs();

echo "Done";