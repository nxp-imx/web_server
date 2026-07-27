//# Copyright 2025 NXP
//# SPDX-License-Identifier: BSD-3-Clause

var ydistance = [{
    name: 'Distance',
    type: 'line',
    data: [0],
    markPoint: {
        data: [
            { type: 'max', name: 'MAX_DATA' },
            { type: 'min', name: 'MIN_DATA' }
        ]
    },
    markLine: {
        data: [
            { type: 'average', name: 'AVE_DATA' }
        ]
    },
}];


var xtime = new Array();
var now = new Date();
var data_current=0,data_pre=0;
var real_value =0;
var max_value =100;
var min_dis=0,min_dis_pre=0;
var max_rssi=0,max_rssi_pre=0;
var reset_status=0;
var sca_starting=false;
var myChart_distance = null;

function getChart() {
    if (!myChart_distance) {
        myChart_distance = echarts.init(document.getElementById('chart-distance'), null, {
            renderer: 'canvas',
            useDirtyRect: false
        });
    }
    return myChart_distance;
}

function UpdateData() {
    //update time
    now = new Date();
    if (xtime.length >= 10) {
        xtime.shift();
    }
    xtime.push(now.getHours().toString().padStart(2,'0') + ":" + now.getMinutes().toString().padStart(2,'0') + ":" + now.getSeconds().toString().padStart(2,'0'));
    if (ydistance[0].data.length >= 10) {
        ydistance[0].data.shift();
    }
    data_current = cat_distance_info().toFixed(2);
    if(data_current != 0){
        ydistance[0].data.push(data_current);
        data_pre = data_current;
    }else{
        ydistance[0].data.push(data_pre);
    }
    const red_round = document.getElementById('red-round');
    const yellow_round = document.getElementById('yellow-round');
    if(data_pre < 1){
        red_round.hidden = false;
        yellow_round.hidden = true;
    }else if(data_pre < 2){
        red_round.hidden = true;
        yellow_round.hidden = false;
    }else {
        red_round.hidden = true;
        yellow_round.hidden = true;

    }
}

function start_sca() {
    if (sca_starting) {
        console.log("sca start already in progress, ignoring extra click\n");
        return;
    }
    sca_starting = true;
    reset_status = 0;
    $.ajax({
        type: "GET",
        url: "./php/general_commands.php?command=sh /www/pages/web_server/sh/sca.sh init_one",
        async: false,
        dataType: "json",
        success: function(data) {
            reset_status = 2;
        },
        complete: function() {
            sca_starting = false;
        }
    });
    console.log("sca start init\n");
}

function reset_sca() {
    reset_status = 0;
    $.ajax({
        type: "GET",
        url: "./php/general_commands.php?command=sh /www/pages/web_server/sh/sca.sh reset",
        async: false,
        dataType: "json",
        success: function(data) {
            reset_status = 1;
        }
    });
    console.log("sca reset\n");
}

setInterval(function() {
    //Update chart
    if(reset_status!=0){
        UpdateData();
    }
    getChart().setOption({
        xAxis: [{
            type: 'category',
            boundaryGap: false,
            data: xtime
        }],
        grid: {
            top: '20%',
            bottom: '15%',
            left: '15%'
        },
        series: ydistance
    });
}, 3500);