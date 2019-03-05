<?php

namespace OIP\Helpers;

use Bitrix\Main\Application;
class Helper
{
    protected $db;

    public function __construct()
    {
        $this->db = Application::getConnection();
    }

    protected function getDB() {
        return $this->db;
    }

    /**
     * @param \CDBResult $dbResult
     * @return array $arResult
     * */
    protected function dbResultFetch($dbResult) {
        $arResult = [];
        while($res = $dbResult->GetNext()) {
            $arResult[] = $res;
        }

        return $arResult;
    }

    /**
     * @param string $IDs
     * @return array $files
    */
    protected function getFiles($IDs) {
        $files = [];
        $dbFile = \CFile::GetList([],['@ID'=>$IDs]);
        while($tFile = $dbFile->Fetch()) {
            $file = [
                'ID' => $tFile['ID'],
                'NAME' => $tFile['ORIGINAL_NAME'],
                'FILE_NAME' => $tFile['FILE_NAME'],
                'SRC' => '/upload/'.$tFile['SUBDIR'].'/'.$tFile['FILE_NAME'],
                'WIDTH' => $tFile['WIDTH'],
                'HEIGHT' => $tFile['HEIGHT'],
                'CONTENT_TYPE' => $tFile['CONTENT_TYPE'],
            ];
            $files[$file['ID']] = $file;
        }
        return $files;
    }
}