<?php

class GKDB {
    private static $connectCount = 0;
    private static $_instance = null;

    private $link = null;

    private function __construct() {
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new GKDB();
        }

        return self::$_instance;
    }

    public static function getLink() {
        return self::getInstance()->connect();
    }

    public static function getConnectCount() {
        return self::$connectCount;
    }

    public function connect() {
        if ($this->link != null) {
            try {
                mysqli_get_server_info($this->link);

                return $this->link;
            } catch (Exception $exc) {
            }
        }
        $dbHost = constant('CONFIG_HOST');
        $dbUser = constant('CONFIG_USERNAME');
        $dbPwd = constant('CONFIG_PASS');
        // DEBUG // echo 'connect '.$dbUser.'@'.$dbHost.' using '.$dbPwd;
        try {
            $this->link = mysqli_connect($dbHost, $dbUser, $dbPwd);
            if (!$this->link) {// lets retry
                $this->link = mysqli_connect($dbHost, $dbUser, $dbPwd);
                if (!$this->link) {
                    throw new Exception('Unable to join database server');
                }
            }
            $dbName = constant('CONFIG_DB');
            $db_select = mysqli_select_db($this->link, $dbName);
            if (!$db_select) {
                throw new Exception('Unknown database "'.$dbName.'" : '.mysqli_errno($this->link));
            }
            $this->link->set_charset(constant('CONFIG_CHARSET'));
            $this->link->query("SET time_zone = '".constant('CONFIG_TIMEZONE')."'");
            ++self::$connectCount;

            return $this->link;
        } catch (Exception $exc) {
            $errorId = uniqid('GKIE_');
            $errorMessage = 'DB ERROR '.$errorId.' - '.$exc->getMessage();
            error_log($errorMessage);
            error_log($exc);
            $this->link = null; // do not reuse link on error
            throw new Exception($errorMessage);
        }
    }

    public function close() {
        if (!isset($this->link)) {
            return;
        }
        mysqli_close($this->link);
        unset($this->link);
    }

    public function __clone() {
        throw new Exception("Can't clone a singleton");
    }
}
