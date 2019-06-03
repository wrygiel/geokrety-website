<?php

namespace Consistency;

// src : https://stackoverflow.com/questions/535020/tracking-the-script-execution-time-in-php
class ExecutionTime {
     private $startTime;
     private $startMicroTime;
     private $endTime;
     private $endMicroTime;

     public function start(){
         $this->startTime = getrusage();
         $this->startMicroTime = microtime(true);
     }

     public function end(){
         $this->endTime = getrusage();
         $this->endMicroTime = microtime(true);
     }

     private function runTime($ru, $rus, $index) {
         return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
     }

     public function __toString(){
         $diff = round($this->endMicroTime - $this->startMicroTime, 2);
         return "This process used " . $this->runTime($this->endTime, $this->startTime, "utime") .
        " ms for its computations\nIt spent " . $this->runTime($this->endTime, $this->startTime, "stime") .
        " ms in system calls / Execution time $diff seconds<br/>\n";
     }
 }