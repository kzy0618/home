<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/17/18
 * Time: 3:40 PM
 */

namespace OCA\Home\Db;


use OCP\AppFramework\Db\Mapper;
use OCP\IDb;
use OCP\ILogger;
use PDO;

class DbHandler extends Mapper
{

    private $logger;
    private $appName;

    /**
     * RecordingMapper constructor.
     * @param ILogger $logger
     * @param $AppName
     * @param IDb $db
     */
    public function __construct(ILogger $logger, $AppName, IDb $db)
    {
        parent::__construct($db, "recorder_recordings", null);
        $this->logger = $logger;
        $this->appName = $AppName;
    }

    public function log($message) {
        $this->logger->error($message, ['app' => $this->appName]);
    }

    /**
     * @return array|string the results in an array or string "none" if no displayable recordings found
     */
    public function getDisplayableRecordings () {
        $sql = "SELECT * FROM oc_recorder_recordings WHERE is_representative = TRUE OR is_standalone = TRUE ";
        $rows = $this->execute($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false or count($rows) === 0) {
            return "none";
        }
        $results = [];
        foreach ($rows as $row) {
            $temp = new RecordingGeoDTO();
            $temp->id = $row['id'];
            $temp->content = $row['content'];
            $temp->datetime = $row['upload_time'];
            $temp->standaloneLon = $row['longitude'];
            $temp->standaloneLat = $row['latitude'];
            $temp->cityName = $row['city_name'];
            $temp->cityLon = $row['city_lon'];
            $temp->cityLat = $row['city_lat'];
            $temp->suburbName = $row['suburb_name'];
            $temp->suburbLon = $row['suburb_lon'];
            $temp->suburbLat = $row['suburb_lat'];
            $temp->isStandalone = $row['is_standalone'];
            $temp->isRepresentative = $row['is_representative'];
            $results[] = $temp;
        }
        return $results;
    }

    /**
     * @param $id
     * @return string return the internal path if found in oc_filecache, 'recycle' if in recycle bin, 'deleted' if the file has been deleted permanently
     */
    public function downloadVerifier($id) {
        // SELECT f.path from vonz.oc_recorder_recordings as r, vonz.oc_filecache as f WHERE r.id = 22 AND r.filename = f.name;
        $sql = "SELECT f.path FROM oc_recorder_recordings AS r, oc_filecache AS f WHERE r.id = ? AND r.filename = f.name";
        $row = $this->execute($sql, [$id])->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            return $row['path']; // internal path
        } else {
            // check if the input id is valid
            $checkIfIDValid = "SELECT COUNT(*) FROM oc_recorder_recordings WHERE id = ?";
            $count = $this->execute($checkIfIDValid, [$id])->fetchColumn();
            if ($count == 0) {
                return "deleted"; // not a valid pk
            }

            // if not in oc_filecache
            $result = $this->isPermanentlyDeleted($id);
            if ($result == 1) {
                // ONLY MAPUTIL CAN DELETE ROWS
                $this->log("UPDATED ROW => DELETED PERMANENTLY : NOT FOUND!!! row id : ".($id));
                return "deleted"; // deleted permanently
            } else {
                return "recycle"; // in recycle bin
            }
        }
    }

    /**
     * @param $id
     * @return mixed, 1 if permanently deleted, 0 if not, might throw exceptions
     */
    private function isPermanentlyDeleted($id) {
        // SELECT COUNT(*) FROM vonz.oc_recorder_recordings WHERE NOT EXISTS(
        //	SELECT NULL FROM vonz.oc_filecache f WHERE filename = f.name
        //    ) AND NOT EXISTS (
        //    SELECT NULL FROM vonz.oc_files_trash f WHERE filename = f.id
        //    ) AND id = 5;
        $sql = "SELECT COUNT(*) FROM oc_recorder_recordings WHERE NOT EXISTS(
                    SELECT NULL FROM oc_filecache f WHERE filename = f.name
                    ) AND NOT EXISTS (
                    SELECT NULL FROM oc_files_trash f WHERE filename = f.id
                    ) AND id = ?";
        $result = $this->execute($sql, [$id]);
        $numberOfRows = $result->fetchColumn();
        return $numberOfRows;
    }

    public function deleteFileRowInFilecacheByName($name) {
        $sql = "DELETE FROM oc_filecache WHERE name = ?";
        $this->execute($sql, [$name]);
    }

    public function deleteFileRowInFileTrashBinByName($name) {
        $sql = "DELETE FROM oc_files_trash WHERE id = ?";
        $this->execute($sql, [$name]);
    }

}