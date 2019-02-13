<?php

namespace Otdel\Helpers;

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
}