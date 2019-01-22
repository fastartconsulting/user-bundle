<?php

namespace FAC\UserBundle\Utils;


class Utils{

    static public function getCurrentTime () {

        $current_time = new \DateTime();
        $current_time->setTimestamp(time())->getTimestamp();

        return $current_time;
    }

    public static function checkId($id){

        if(is_null($id))
            return false;

        if(!is_numeric($id))
            return false;

        if($id < 1)
            return false;

        return true;
    }

    public static function checkNum($val) {

        if(is_null($val))
            return false;

        if(!is_numeric($val))
            return false;

        return true;
    }

    public static function domainExists($email){
        $domain = explode('@', $email);
        $arr= dns_get_record($domain[1],DNS_MX);
        if(!empty($arr)) {
            return true;
        }

        return false;
    }

    public static function checkEmailString($str) {
        if(is_null($str)) {
            return false;
        }

        if(!is_string($str)) {
            return false;
        }

        if(strlen($str) < 1 || strlen($str) > 255) {
            return false;
        }

        $pattern = "/^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/";
        if(!preg_match($pattern, $str)) {
            return false;
        }

        if(!Utils::domainExists($str)) {
            return false;
        }

        return true;
    }

    public static function checkHashString($str) {
        if(is_null($str)) {
            return false;
        }

        if(!is_string($str)) {
            return false;
        }

        if(strlen($str) < 30 || strlen($str) > 500) {
            return false;
        }

        if(addslashes($str) != $str) {
            return false;
        }

        return true;
    }

    public static function checkPasswordString($str) {
        if(is_null($str)) {
            return false;
        }

        if(!is_string($str)) {
            return false;
        }

        if(strlen($str) < 2 || strlen($str) > 255) {
            return false;
        }

        return true;
    }

    public static function getFormattedExceptions(\Exception $e) {
        $exception = array();

        if(!is_null($e)) {
            $exception = array(
                'backtrace'        => $e->getTraceAsString(),
                'file'             => $e->getFile(),
                'line'             => $e->getLine(),
                'exceptionMessage' => $e->getMessage()
            );
        }

        return $exception;
    }
}