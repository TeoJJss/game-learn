<?php 
    function check_badge($point){
        if ($point > 5000){
            $lvl = 5;
        }else if ($point>2000){
            $lvl = 4;
        }else if ($point>700){
            $lvl = 3;
        }else if ($point>300){
            $lvl = 2;
        }else{
            $lvl = 1;
        }
        return $lvl;
    }
?>