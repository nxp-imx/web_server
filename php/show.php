<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */

    $output=null;
    $retval=null;
    exec('connmanctl services;', $output, $retval);  
    
    for ($i=1;$i<count($output);$i++)
    {
        $output[$i] = trim($output[$i]);
        $output[$i] = trim($output[$i],"*A");
        $output[$i] = trim($output[$i]);
        
        if (stripos($output[$i],"hidden"))
        {
            unset($output[$i]);
        }else
        {
            $wifi_place = strpos($output[$i],"wifi_d");
             
            echo rtrim(substr($output[$i],0,$wifi_place-1));
            echo "<br>";
            echo substr($output[$i],$wifi_place-strlen($output[$i]));
            echo "<br>";
        }
    } 
?>
