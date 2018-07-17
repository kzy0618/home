<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/17/18
 * Time: 3:24 PM
 */

namespace OCA\Home\Controller;


use OCA\Home\Db\DbHandler;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\Files\NotFoundException;
use OCP\ILogger;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;

class MapController extends Controller
{

    private $logger;
    private $dbHandler;

    public function __construct($AppName, IRequest $request, ILogger $logger, DbHandler $dbHandler){
        parent::__construct($AppName, $request);
        $this->logger = $logger;
        $this->dbHandler = $dbHandler;
    }

    public function log($msg) {
        $this->logger->error($msg, ['app' => $this->appName]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     * @return DataResponse if found any displayable items, they will be put inside the DataResponse, otherwise DataResponse contains a string 'none'
     */
    public function getRecordings(){
        return new DataResponse($this->dbHandler->getDisplayableRecordings());
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     * @param $id
     * @return Http\DataDownloadResponse|DataResponse either 'recycle' or 'deleted' will be generated via DataResponse upon failure, otherwise a DataDownloadResponse will be generated.
     */
    public function download($id){
        $result = $this->dbHandler->downloadVerifier($id);
        if ($result === "recycle") {
            return new DataResponse(["recycle"], Http::STATUS_FORBIDDEN); // 403
        } elseif ($result === "deleted") {
            return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
        } else {
            return $this->generateDownloadResponse($id, $result);
        }
    }

    private function generateDownloadResponse ($id, $path) {
        try {
            /** @noinspection PhpUndefinedClassInspection */
            $storage = \OC::$server->getUserFolder('Frenchalexia')->getStorage();
            $temp = null;
            $handle = null;
            $size = 1;
            $pos = 0;
            while ($pos !== $size) {
                $isExisting = $storage->file_exists($path);
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file exist : ".$isExisting);
                if (!$isExisting) {
                    throw new NotFoundException("deleted");
                }
                $handle = $storage->fopen($path, "rb"); // b for Binary fread()
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file read : ".$handle);
                $size = $storage->filesize($path);
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file size : ".$size);
                $temp = fread($handle, $size);
                $pos = ftell($handle);
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file pointer pos : ".$pos);
            }
            fclose($handle);
            $pathFragments = explode("/", $path);
            return new Http\DataDownloadResponse($temp, end($pathFragments), "audio/wav");
        } catch (NotFoundException $e) {
            return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
        }
    }

}