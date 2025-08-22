//# Copyright 2023 NXP
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

var rssi = [{
    name: 'RSSI',
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
var data_current =[
    ['0','0'],
    ['0','0'],
    ['0','0']
];
var real_value =0;
var max_value =100;
var min_dis=0,min_dis_pre=0;
var max_rssi=0,max_rssi_pre=0;
var reset_status=0;
//Update data
function UpdateData() {
    //update time
    var position_x;
    var position_y;

    now = new Date();
    if (xtime.length >= 10) {
        xtime.shift();
    }
    xtime.push(now.getHours().toString().padStart(2,'0') + ":" + now.getMinutes().toString().padStart(2,'0') + ":" + now.getSeconds().toString().padStart(2,'0'));
    if (ydistance[0].data.length >= 10) {
        ydistance[0].data.shift();
    }
    data_current = cat_distance_info();
    min_dis = Math.min(data_current[0][0],data_current[1][0],data_current[2][0]);
    min_dis = min_dis/100; //convert cm to m
    if(min_dis== 0){
        ydistance[0].data.push(min_dis_pre);
    }else{
        ydistance[0].data.push(min_dis);
        min_dis_pre = min_dis;
    }
    if (rssi[0].data.length >= 10) {
        rssi[0].data.shift();
    }
    max_rssi = Math.max(data_current[0][1],data_current[1][1],data_current[2][1]);
    if(max_rssi == 0){
        rssi[0].data.push(max_rssi_pre);
    }else{
        rssi[0].data.push(max_rssi);
        max_rssi_pre = max_rssi;
    }
    if((data_current[0][0]!=0)&&(data_current[1][0]!=0)){
        [position_x, position_y] = trilaterate(data_current[0][0]/10,data_current[1][0]/10,data_current[2][0]/10);
        const dot = document.getElementById('dot');
        dot.style.left = `${position_x}vh`;
        dot.style.top = `${60-position_y}vh`;
    }
}

function trilaterate(d1, d2, d3) {
  
    const x1 = 47.5, y1 = 30;
    const x2 = 52.5, y2 = 32.5;
    const x3 = 52.5, y3 = 27.5;
  
    const a1 = 2 * (x2 - x1);
    const b1 = 2 * (y2 - y1);
    const c1 = d1 * d1 - d2 * d2 - x1 * x1 + x2 * x2 - y1 * y1 + y2 * y2;
  
    const a2 = 2 * (x3 - x1);
    const b2 = 2 * (y3 - y1);
    const c2 = d1 * d1 - d3 * d3 - x1 * x1 + x3 * x3 - y1 * y1 + y3 * y3;
  
    const det = a1 * b2 - a2 * b1;
  
    if (Math.abs(det) < 1e-10) {
      throw new Error("invalid data");
    }
  
    const x = (c1 * b2 - c2 * b1) / det;
    const y = (a1 * c2 - a2 * c1) / det;
  
    return [x.toFixed(2), y.toFixed(2)];
  }

  function start_sca() {
    reset_status = 0;
    $.ajax({
        type: "GET",
        url: "./php/general_commands.php?command=sh /www/pages/web_server/sh/sca.sh init",
        async: false,
        dataType: "json",
        success: function(data) {
            reset_status = 2;
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
    //reinit charts
    var myChart_distance = echarts.init(document.getElementById('chart-distance'), null, {
        renderer: 'canvas',
        useDirtyRect: false
    });

    myChart_distance.setOption({
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

    var myChart_rssi = echarts.init(document.getElementById('chart-rssi'), null, {
        renderer: 'canvas',
        useDirtyRect: false
    });

    myChart_rssi.setOption({
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
        series: rssi
    });
}, 1200);