//# Copyright 2023 NXP
//# SPDX-License-Identifier: BSD-3-Clause

function general_commands(command) {
    var data_recv;

    $.ajax({
        type: "GET",
        url: "./php/general_commands.php?command=" + command,
        async: true,
        dataType: "json",
        success: function(data) {
            data_recv = data;
            console.log(data_recv);
        }
    });
    return data_recv;
}

//get temprature data
//return：type is array,for example '31.000°C'
function cat_temp_info() {
    var data_recv;

    $.ajax({
        type: "GET",
        url: "./php/temperature.php",
        async: false,
        dataType: "json",
        success: function(data) {
            data_recv = data;
        }
    });

    return data_recv;
}

function cat_bt_info() {
    var data_recv;

    $.ajax({
        type: "GET",
        url: "./php/bluetooth.php",
        async: false,
        dataType: "json",
        success: function(data) {
            data_recv = data;
        }
    });

    return data_recv;
}

//get acc data
//return：type is array，for example：['2','100','50']
function cat_acc_info() {
    var data_recv;

    $.ajax({
        type: "GET",
        url: "./php/accelerometer.php",
        async: false,
        dataType: "json",
        success: function(data) {
            data_recv = data;
        }
    });

    return data_recv;
}

//get CPU frequence data
//return：type is array，for example：['1200000', '1200000', '1200000', '1200000']
function cat_cpu_freq() {
    var cpu_freq = [0, 0, 0, 0];

    $.ajax({
        type: "GET",
        url: "./php/cpu_freq.php",
        async: false,
        dataType: "json",
        success: function(data) {
            cpu_freq[0] = data[0];
            cpu_freq[1] = data[1];
            cpu_freq[2] = data[2];
            cpu_freq[3] = data[3];
        }
    });

    return cpu_freq;
}

function cat_cpu_info() {
    var data_recv;

    $.ajax({
        type: "GET",
        url: "./php/lscpu.php",
        async: false,
        dataType: "json",
        success: function(data) {
            data_recv = data;
        }
    });

    return data_recv;
}

//Get cpu information
//input：raw data  from cat_cpu_info();
//     cpu_info range:    Architecture, CPU op-mode(s), Byte Order, CPU(s), On-line CPU(s) list,
//                        Vendor ID, Model name, Model, Thread(s) per core, Core(s) per cluster, 
//                        Socket(s), Cluster(s), Stepping, CPU(s) scaling MHz, CPU max MHz, CPU min MHz, 
//                        BogoMIPS, Flags, L1d cache, L1i cache, L2 cache, NUMA node(s), NUMA node0 CPU(s), 
//                        Vulnerability Itlb multihit, Vulnerability L1tf, Vulnerability Mds, 
//                        Vulnerability Meltdown, Vulnerability Mmio stale data, Vulnerability Retbleed, 
//                        Vulnerability Spec store bypass, Vulnerability Spectre v1, Vulnerability Spectre v2, 
//                        Vulnerability Srbds, Vulnerability Tsx async abort
//return：string data with CPU information
function get_cpu_info(data, cpu_info) {
    var data_name;
    var data_value;
    for (var i = 0; i < data.length; i++) {
        //find ":"
        var spliter_location = data[i].indexOf(":");
        data_name = data[i].slice(0, spliter_location);
        data_value = data[i].slice(spliter_location + 1);
        data_value = data_value.trimStart();
        if (cpu_info == data_name) {
            return data_value;
        }
    }
}

function cat_ram_info() {
    var data_recv;

    $.ajax({
        type: "GET",
        url: "./php/meminfo.php",
        async: false,
        dataType: "json",
        success: function(data) {
            data_recv = data;
        }
    });

    return data_recv;
}

//Get ram information
//input： raw data ram_info
//         ram_info range:MemTotal, MemFree, MemAvailable, Buffers, Cached, SwapCached, Active, 
//                        Inactive, Active(anon), Inactive(anon), Active(file), Inactive(file), 
//                        Unevictable, Mlocked, SwapTotal, SwapFree, Dirty, Writeback, AnonPages, 
//                        Mapped, Shmem, KReclaimable, Slab, SReclaimable, SUnreclaim, KernelStack, 
//                        PageTables, SecPageTables, NFS_Unstable, Bounce, WritebackTmp, CommitLimit, 
//                        Committed_AS, VmallocTotal, VmallocUsed, VmallocChunk, Percpu, HardwareCorrupted, 
//                        AnonHugePages, ShmemHugePages, ShmemPmdMapped, FileHugePages, FilePmdMapped, 
//                        CmaTotal, CmaFree, HugePages_Total, HugePages_Free, HugePages_Rsvd, HugePages_Surp, 
//                        Hugepagesize, Hugetlb
//return：string data with ram information 
function get_ram_info(data, ram_info) {
    var data_name;
    var data_value;

    for (var i = 0; i < data.length; i++) {
        //find ":"
        var spliter_location = data[i].indexOf(":");
        data_name = data[i].slice(0, spliter_location);
        data_value = data[i].slice(spliter_location + 1);
        data_value = data_value.trimStart();

        if (ram_info == data_name) {
            return data_value;
        }
    }
}

//reflash Basic Information
function refresh_list() {
    var ram_data = cat_ram_info();
    var cpu_data = cat_cpu_info();
    var cpu_freq = cat_cpu_freq()

    document.getElementById("cpucores").innerHTML = "CPU(s) :";
    document.getElementById("Modelname").innerHTML = "Model name :";
    document.getElementById("cpufreq1").innerHTML = "CPU1 freq :";
    document.getElementById("cpufreq2").innerHTML = "CPU2 freq :";
    document.getElementById("MemTotal").innerHTML = "Mem Total :";
    document.getElementById("MemFree").innerHTML = "Mem Free :";
    document.getElementById("MemAvailable").innerHTML = "Mem Available :";
    document.getElementById("Buffers").innerHTML = "Buffers :";

    document.getElementById("cpucores").innerHTML += get_cpu_info(cpu_data, "CPU(s)");
    document.getElementById("Modelname").innerHTML += get_cpu_info(cpu_data, "Model name");
    document.getElementById("cpufreq1").innerHTML += cpu_freq[0];
    document.getElementById("cpufreq2").innerHTML += cpu_freq[1];
    document.getElementById("MemTotal").innerHTML += get_ram_info(ram_data, "MemTotal");
    document.getElementById("MemFree").innerHTML += get_ram_info(ram_data, "MemFree");
    document.getElementById("MemAvailable").innerHTML += get_ram_info(ram_data, "MemAvailable");
    document.getElementById("Buffers").innerHTML += get_ram_info(ram_data, "Buffers");
}

function formatStringLen(strVal, len, padChar) {
    padChar = padChar || "*";
    if (!strVal) {
        return padChar.repeat(len);
    } else {
        const strLen = strVal.length;
        if (strLen > len) {
            return strVal.substring(0, len);
        } else if (strLen < len) {
            return strVal.padEnd(len, padChar);
        } else {
            return strVal;
        }
    }
}

//Get RAM's usage
function get_ram_usage() {
    var ram_used = 0;

    $.ajax({
        type: "GET",
        url: "./php/ram_info.php",
        async: false,
        success: function(data) {
            data = data.split(' ')
            var ram_info = {}; 
            var ram_info_name = ['total', 'used', 'free', 'shared', 'buff/cache', 'available'];
            var ram_info_value = [];
            for (var i = 1; i < data.length; i++) {
                if (data[i] != '') {
                    ram_info_value.push(data[i]);
                }
            }
            ram_info = ArrayToObj(ram_info_name, ram_info_value);
            ram_used = (ram_info.used / ram_info.total) * 100;
        }
    });
    return ram_used;
}

//Get all CPUs' usage
function get_cpu_usage() {
    var cpu_used = 0;

    $.ajax({
        type: "GET",
        url: "./php/cpu_info.php",
        dataType: "json",
        async: false,
        success: function(data) {
            var cpu_info_arr = Array.from(new Array(data.length - 1), () => new Array(0)); //用于存储所有的CPU信息

            for (var i = 1; i < data.length; i++) {
                data[i] = data[i].split(" ");
                for (var j = 0; j < data[i].length; j++) {
                    if (data[i][j] != '') {
                        cpu_info_arr[i - 1].push(data[i][j]);
                    }
                }
            }
            for (var a = 0; a < cpu_info_arr.length; a++) {
                cpu_used += Number(cpu_info_arr[a][2]);
            }
        }
    });

    return cpu_used;
}

// Merge two array to one
function ArrayToObj(arrA, arrB) {
    var info = {};

    for (var i = 0; i < arrA.length; i++) {
        info[arrA[i]] = arrB[i];
    }

    return info;
}

var data_former = [(0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0)];
var data_latter = [(0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0), (0, 0, 0, 0, 0, 0, 0, 0, 0, 0)];

function cat_single_cpu() {
    var data_arr = [];

    $.ajax({
        type: "GET",
        url: "./php/cpu_single.php",
        async: false,
        success: function(data) {
            data_arr = DataArrProc(data);
        }
    });

    return data_arr;
}

//Get single CPU usage
function get_single_cpu(data, cpux) {
    var data_changed = [];
    var data_changed = [];

    data_former[cpux] = data_latter[cpux];
    data_latter[cpux] = data[cpux];
    data_changed[cpux] = Array_minus(data_latter[cpux], data_former[cpux]);
    var data_changed_sum = 0;
    var cpu_usage = 0;
    for (var i = 0; i < data_changed[cpux].length; i++) {
        data_changed_sum += data_changed[cpux][i];
    }
    cpu_usage = (1 - (data_changed[cpux][3] / data_changed_sum)) * 100;

    // console.log('cpu_free:' + data_changed[cpux][3]);
    // console.log('cpu_sum:' + data_changed_sum);
    // console.log("data_former:" + data_former[cpux]);
    // console.log("data_latter:" + data_latter[cpux]);
    // console.log("data_changed:" + data_changed[cpux]);
    // console.log('cpu' + (cpux - 1) + ` usage:` + cpu_usage);

    return cpu_usage;
}

//process data from ajax 
function DataArrProc(data) {
    data_arr = JSON.parse(data);
    var data_arr2 = [];
    var data_arr3 = [];
    var data_temp = [];

    for (var i = 0; i < 5; i++) {
        data_temp = data_arr[i].split(' ');
        data_arr2 = []; 
        for (var j = 1; j < data_temp.length; j++) {
            if (data_temp[j] != '') {
                data_arr2.push(Number(data_temp[j]));
            }
        }
        data_arr3.push(data_arr2);
    }

    return data_arr3;
}

//input：arr1，arr2
//return：arr = arr1 - arr2
function Array_minus(arr1, arr2) {
    var arr_result = [];

    if (arr1.length == arr2.length) {
        for (var i = 0; i < arr1.length; i++) {
            arr_result.push(arr1[i] - arr2[i]);
        }
    }
    return arr_result;
}

function cat_cpu_usage() {
    var cpu_usage = [];

    $.ajax({
        type: "GET",
        url: "top.php",
        async: false,
        dataType: "json",
        success: function(data) {
            cpu_usage = data;
        }
    });

    return cpu_usage;
}

function calc_cpu_usage(data, cpux) {
    if (cpux == 0) {
        str = data[2].replace(/\s*/g, "");
        var cpu_usage = 100 - Number(str.slice(str.search('%Cpu0:') + 6, str.search('%Cpu1:')).replace(/[a-zA-Z]/g, '').split(',')[3]);
        return cpu_usage;
    } else if (cpux == 1) {
        str = data[2].replace(/\s*/g, "");
        var cpu_usage = 100 - Number(str.slice(str.search('%Cpu1:') + 1 + 6).replace(/[a-zA-Z]/g, '').split(',')[3]);
        return cpu_usage;
    } else if (cpux == 2) {
        str = data[3].replace(/\s*/g, "");
        var cpu_usage = 100 - Number(str.slice(str.search('%Cpu2:') + 6, str.search('%Cpu3:')).replace(/[a-zA-Z]/g, '').split(',')[3]);
        return cpu_usage;
    } else if (cpux == 3) {
        str = data[3].replace(/\s*/g, "");
        var cpu_usage = 100 - Number(str.slice(str.search('%Cpu3:') + 1 + 6).replace(/[a-zA-Z]/g, '').split(',')[3]);
        return cpu_usage;
    }
}

function cat_sh_cpu_usage() {
    var cpu_usage = [];

    $.ajax({
        type: "GET",
        url: "cpu.php",
        async: false,
        dataType: "json",
        success: function(data) {
            cpu_usage = data;
        }
    });

    return cpu_usage;
}

function cat_ps() {
    var cpu_usage = [];

    $.ajax({
        type: "GET",
        url: "./php/ps.php",
        async: false,
        dataType: "json",
        success: function(data) {
            cpu_usage = data;
        }
    });

    return cpu_usage;
}

//cpu0-1;cpu1-2;cpu2-3;cpu3-4;
function calc_cpu_usage(data, cpux) {
    var No = cpux - 1;

    cpu_now[No] = data[cpux].slice(5).split(" ").map(Number);
    cpu_sum[No] = arr_sum(cpu_now[No]);
    cpu_delta[No] = cpu_sum[No] - cpu_last_sum[No];
    cpu_idle[No] = cpu_now[No][3] - cpu_last[No][3];
    cpu_used[No] = cpu_delta[No] - cpu_idle[No];
    cpu_usage[No] = 100 * (cpu_used[No] / cpu_delta[No]);

    cpu_last[No] = cpu_now[No];
    cpu_last_sum[No] = cpu_sum[No];

    return cpu_usage[No];
}

function arr_sum(arr) {
    var sum = 0;
    for (var i = 0; i < arr.length; i++) {
        sum = sum + arr[i];
    }
    return sum;
}