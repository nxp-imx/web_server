//# Copyright 2023 NXP
//# SPDX-License-Identifier: BSD-3-Clause

var ydata = [{ 
    name: 'RAM usage',
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

var y0data = [{ 
    name: 'CPU0 usage',
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

var y1data = [{
    name: 'CPU1 usage', 
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

var xdata = new Array(); 
var arrtitle = ['CPU usage']; 
var now = new Date(); 

//Update data
function UpdateData() {
    //update time
    now = new Date();
    if (xdata.length >= 10) {
        xdata.shift();
    }
    xdata.push(now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds()); //格式化x轴时间信息
    //update ram information
    if (ydata[0].data.length >= 10) {
        ydata[0].data.shift();
    }
    ydata[0].data.push(get_ram_usage().toFixed(2));
    //update cpu
    single_cpu_data = cat_ps();
    //update cpu1
    if (y0data[0].data.length >= 10) {
        y0data[0].data.shift();
    }
    y0data[0].data.push(calc_cpu_usage(single_cpu_data, 1).toFixed(2));
    //update cpu2
    if (y1data[0].data.length >= 10) {
        y1data[0].data.shift();
    }
    y1data[0].data.push(calc_cpu_usage(single_cpu_data, 2).toFixed(2));
}

//Check TempData，change color according to the value: value<= 30 :green，30<value<40: orange，value >=40 red
function CheckTemper(Temp) {
    if (Temp > 30 & Temp < 40) {
        myChart_temper.setOption({
            series: [{
                itemStyle: {
                    color: '#FF8C00'
                }
            }]
        });
    } else if (Temp >= 40) {
        myChart_temper.setOption({
            series: [{
                itemStyle: {
                    color: '#FF0000'
                }
            }]
        });
    } else {
        myChart_temper.setOption({
            series: [{
                itemStyle: {
                    color: '#007F00'
                }
            }]
        });
    }
}

setInterval(function() {
    //Update chart
    UpdateData();
    //reinit charts
    var myChart_ram = echarts.init(document.getElementById('chart-ram'), null, {
        renderer: 'canvas',
        useDirtyRect: false
    });
    var myChart_cpu0 = echarts.init(document.getElementById('chart-cpu0'), null, {
        renderer: 'canvas',
        useDirtyRect: false
    });
    var myChart_cpu1 = echarts.init(document.getElementById('chart-cpu1'), null, {
        renderer: 'canvas',
        useDirtyRect: false
    });
    var myChart_temper = echarts.init(document.getElementById("chart-temper"));
    var myChart_acc = echarts.init(document.getElementById('chart-acc'));

    //input xdata,ydata 
    myChart_ram.setOption({
        xAxis: [{
            type: 'category',
            boundaryGap: false,
            data: xdata
        }],
        grid: {
            top: '20%',
            bottom: '15%',
            left: '15%'
        },
        series: ydata
    });
    myChart_cpu0.setOption({
        xAxis: [{
            type: 'category',
            boundaryGap: false,
            data: xdata
        }],
        title: {
            text: 'CPU1 usage',
            textStyle: {
                color: '#000000'
            },
            left: "center",
            top: "top"
        },
        grid: {
            top: '20%',
            bottom: '15%',
            left: '15%'
        },
        series: y0data
    });
    myChart_cpu1.setOption({
        xAxis: [{
            type: 'category',
            boundaryGap: false,
            data: xdata
        }],
        grid: {
            top: '20%',
            bottom: '15%',
            left: '15%'
        },
        title: {
            text: 'CPU2 usage',
            textStyle: {
                color: '#000000'
            },
            left: "center",
            top: "top"
        },
        series: y1data
    });
    var temperature = (Number(cat_temp_info()[0].replace("°C", "")) - 6).toFixed(1);
    var acc_data = cat_acc_info();

    CheckTemper(temperature);

    myChart_temper.setOption({
        title: {
            text: "Temperature",
            textStyle: {
                color: '#000000'
            },
            left: "center",
            top: "top"
        },
        series: [{
            data: [{
                value: temperature
            }]
        }]
    });

    myChart_acc.setOption({
        title: {
            text: "Accelerator",
            textStyle: {
                color: '#000000'
            },
            left: "center",
            top: "top"
        },
        grid: {
            bottom: '1%'
        },
        series: [{
            type: 'bar',
            stack: 'Total',
            label: {
                show: true,
                formatter: '{b} : {c}',
                position: 'right',
                fontSize: 15
            },
            data: [{
                value: Number(acc_data[0]),
                itemStyle: {
                    color: '#c9d200'
                }
            }, {
                value: Number(acc_data[1]),
                itemStyle: {
                    color: '#7bb1db'
                }
            }, {
                value: Number(acc_data[2]) + 982,
                itemStyle: {
                    color: "#ffb500"
                }
            }, ],
            barWidth: 25,
            barGap: 40,
            itemStyle: {
                borderRadius: 5
            }
        }]
    });
    refresh_list();
}, 1000); 