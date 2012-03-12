<?php

class Utils
{
        #public $currentSection;

        function setCurrentSection($s){
            $this->currentSection = $s;
        }

        function classForSection($section){

            if($section == $this->currentSection){
                echo " class=\"current\"";
                return;
            }

            return;
        }
}

?>