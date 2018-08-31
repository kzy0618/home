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
     * download audio only
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     * @param $id
     * @return Http\DataDownloadResponse|DataResponse either 'recycle' or 'deleted' will be generated via DataResponse upon failure, otherwise a DataDownloadResponse will be generated.
     * @throws \Exception
     */
    public function downloadAudioOnly($id){
        $result = $this->dbHandler->downloadVerifier($id);
        if ($result === "recycle") {
            return new DataResponse(["recycle"], Http::STATUS_FORBIDDEN); // 403
        } elseif ($result === "deleted") {
            return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
        } else {
            return $this->generateDownloadResponse($id, $result, "audio/wav");
        }
    }

    /**
     * @param $id $id is purely for debug logging purpose, you can pass whatever you want to be logged out
     * @param $path $path relative to Frenchalexia's data dir (i.e., paths should start with "files/...")
     * @param $contentType
     * @param bool $doCleanUp $doCleanUp === true will delete the file in the $path given, after reading it
     * @return Http\DataDownloadResponse|DataResponse
     * @throws \Exception
     */
    private function generateDownloadResponse ($id, $path, $contentType, $doCleanUp = false) {
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
            if ($doCleanUp) {
                $this->log("going to delete temp zip at Frenchalexia's files dir at path : $path");
                $isDeleted = $storage->unlink($path);
                $this->dbHandler->deleteFileRowInFilecacheByName(end($pathFragments));
                if ($isDeleted === false) {
                    throw new \Exception("FAIL TO DELETE TEMP FILE BEFORE GENERATING DATA DOWNLOAD RESPONSE");
                } else {
                    $this->log("going to delete temp zip at Frenchalexia's files_trashbin/files dir");
                    $dir = $storage->opendir("files_trashbin/files");
                    if ($dir === false) {
                        throw new \Exception("FAIL TO OPEN TRASH BIN");
                    }
                    $isDeleted = false;
                    $nameToSearch = explode(".", end($pathFragments))[0];
                    $this->log("FILENAME TO SEARCH IN TRASH BIN : $nameToSearch");
                    while (($file = readdir($dir)) !== false) {
//                        $this->log("LISTING FILES IN TRASH BIN : $file");
                        $targetName = explode(".", $file)[0];
                        if ($targetName === $nameToSearch) {
                            $this->log("FIND THE FILE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! : $file");
                            $this->dbHandler->deleteFileRowInFilecacheByName($file);
                            $isDeleted = $storage->unlink("files_trashbin/files/".$file);
                        }
                    }
                    closedir($dir);
                    $this->dbHandler->deleteFileRowInFileTrashBinByName(end($pathFragments));
                    if ($isDeleted === false) {
                        $this->log("the file $path has gone");
                    } else {
                        $this->log("deleted $path SUCCESSFULLY!!!!!!!!!!!!");
                    }
                }
            }
            return new Http\DataDownloadResponse($temp, end($pathFragments), $contentType);
        } catch (NotFoundException $e) {
            return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
        }
    }

    /**
     * Pessimistically attempt to undertake a download
     * Download both audio and text files, returning a zip containing a pair of audio and text upon success
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     * @param $id
     * @return Http\DataDownloadResponse|DataResponse
     * @throws \Exception
     */
    public function download($id) {
        return $this->bulkDownload([$id]);
    }

    /**
     * Pessimistically attempt to undertake a bulk download
     * Download both audio and text files, returning a zip upon success
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     * @param array $idsToDownload
     * @return Http\DataDownloadResponse|DataResponse
     * @throws \Exception
     */
    public function bulkDownload(array $idsToDownload){
        $owncloudDataRoot = "/var/www/owncloud/data";
        /** @noinspection PhpUndefinedClassInspection */
        $folder = \OC::$server->getUserFolder('Frenchalexia');
        $folderPath = $folder->getPath();
        $this->log("DEBUGGING IN BULK DOWNLOAD: folder full path: $owncloudDataRoot$folderPath");
        $folderFullPath = $owncloudDataRoot.$folderPath;
        $zip = new \ZipArchive();
        $explodeduuid = explode(".", uniqid("temp", true));
        $tempname = $explodeduuid[0].$explodeduuid[1].".zip";
        $filename = $folderFullPath."/".$tempname;
        $filenameRelativeToFrenchalexiaDir = "files/".$tempname;
        $this->log("DEBUGGING IN BULK DOWNLOAD: file full path: $filename");
        $this->log("DEBUGGING IN BULK DOWNLOAD: file relative path: $filenameRelativeToFrenchalexiaDir");

        // create zip
        $folder->newFile($tempname);
        if ($zip->open($filename) !== TRUE) {
            $this->log("DEBUGGING IN BULK DOWNLOAD: cannot open <$filename>");
            throw new \Exception("cannot open <$filename>");
        }
        $this->log("DEBUGGING IN BULK DOWNLOAD: temp zip <$filename> created!!!!!!!!!!!!!!!!!");

        try {
            $isErrorOccur = false;
            foreach ($idsToDownload as $id) {
                $result = $this->dbHandler->downloadVerifier($id);
                if ($result === "recycle") {
                    $isErrorOccur = true;
                    return new DataResponse(["recycle"], Http::STATUS_FORBIDDEN); // 403
                } elseif ($result === "deleted") {
                    $isErrorOccur = true;
                    return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
                } else {
                    $splitPath = explode("/", $result);
                    $this->log("DEBUGGING IN BULK DOWNLOAD: attempt to zip file ".$owncloudDataRoot . $result);
                    $isSuccess = $zip->addFile($owncloudDataRoot . "/Frenchalexia/" . $result, end($splitPath)); // we only care about the origins in Frenchalexia's dir, since they are free from interventions of non-admin users
                    $txtPath = "";
                    for ($i = 0 ; $i < count($splitPath) - 1 ; $i++ ) {
                        $txtPath .= $splitPath[$i] . "/";
                    }
                    $endOfPath = end($splitPath);
                    $txtFilename = explode(".", $endOfPath)[0] . ".txt";
                    $txtPath .= $txtFilename;
                    $isTxtAdded = $zip->addFile($owncloudDataRoot . "/Frenchalexia/" . $txtPath, $txtFilename);
                    if ($isSuccess === false || $isTxtAdded === false) {
                        $isErrorOccur = true;
                        return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
                    }
                }
            }
        } finally {
            $this->log("numfiles: " . $zip->numFiles);
            $this->log("status:" . $zip->status);
            $zip->close();
            if ($isErrorOccur) {
                /** @noinspection PhpUndefinedClassInspection */
                $storage = \OC::$server->getUserFolder('Frenchalexia')->getStorage();
                $this->log("going to delete temp zip at Frenchalexia's files dir at path : $filenameRelativeToFrenchalexiaDir");
                $isDeleted = $storage->unlink($filenameRelativeToFrenchalexiaDir);
                $this->dbHandler->deleteFileRowInFilecacheByName($tempname);
                if ($isDeleted === false) {
                    throw new \Exception("FAIL TO DELETE TEMP FILE BEFORE GENERATING DATA DOWNLOAD RESPONSE");
                } else {
                    $this->log("going to delete temp zip at Frenchalexia's files_trashbin/files dir");
                    $dir = $storage->opendir("files_trashbin/files");
                    if ($dir === false) {
                        throw new \Exception("FAIL TO OPEN TRASH BIN");
                    }
                    $isDeleted = false;
                    $nameToSearch = explode(".", $tempname)[0];
                    $this->log("FILENAME TO SEARCH IN TRASH BIN : $nameToSearch");
                    while (($file = readdir($dir)) !== false) {
//                        $this->log("LISTING FILES IN TRASH BIN : $file");
                        $targetName = explode(".", $file)[0];
                        if ($targetName === $nameToSearch) {
                            $this->log("FIND THE FILE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! : $file");
                            $this->dbHandler->deleteFileRowInFilecacheByName($file);
                            $isDeleted = $storage->unlink("files_trashbin/files/".$file);
                        }
                    }
                    closedir($dir);
                    $this->dbHandler->deleteFileRowInFileTrashBinByName($tempname);
                    if ($isDeleted === false) {
                        $this->log("the file $tempname has gone");
                    } else {
                        $this->log("deleted $tempname SUCCESSFULLY!!!!!!!!!!!!");
                    }
                }
            }
        }

        // mime type application/zip
        return $this->generateDownloadResponse("zip", $filenameRelativeToFrenchalexiaDir, "application/zip", true);
    }

}