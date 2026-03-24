<?php


function ida_density_level($total){

    if($total <= 20){
        return 'NORMAL';
    }

    if($total <= 40){
        return 'MEDIUM';
    }

    if($total <= 80){
        return 'HIGH';
    }

    if($total <= 150){
        return 'CRITICAL';
    }

    return 'SUPER CRITICAL';
}



function ida_performance_risk($weight){

if($weight < 5){
return 'LOW';
}

if($weight < 15){
return 'MEDIUM';
}

return 'HIGH';

}