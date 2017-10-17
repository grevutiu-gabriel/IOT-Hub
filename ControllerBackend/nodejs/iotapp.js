var express    = require("express");
var mysql      = require('mysql');
var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'n0n3wb13z!',
  database : 'iothub'
});

var mysql2 = require('mysql');
var pool = mysql2.createPool({
    connectionLimit : 100,
    host     : 'localhost',
    user     : 'root',
    password : 'n0n3wb13z!',
    database : 'iothub',
    debug    : false 
});

var app = express();

connection.connect(function(err){
if(!err) {
    console.log("Database is connected ... nn");    
} else {
    console.log("Error connecting database ... nn");
    console.log(err.code);  
    console.log(err.fatal);  
}
});

app.post("/dev/:devID/:param",function(req,res){
  var error = false;
    var obj = JSON.parse(req.params.param);
    //console.log(req.params);
    
    try {
        var sqlQuery = ' ';
        connection.query('SELECT d.*, ds.ParamID, ds.Value, ds.Time FROM tDevice as d LEFT JOIN tDeviceSetting as ds ON d.DeviceID = ds.DeviceID WHERE d.SerialNum = ?',[req.params.devID],function(err, rows, fields) {
            var statusCode;
            var returnArray = [];
            //connection.end();
            if (!err){
              if(rows.length < 1){
                error = true;
              }else{
                error = false;
              }
            }else{
              insertLog(2, err.errno);
              error = true;
            }

            if(!error){
              statusCode = 1;
              try {
                var response = JSON.stringify({ status: statusCode, data: rows[2].Value, onoff: rows[0].Value })
                insertLog(1, JSON.stringify(rows));
                console.log("Success");
              } catch(err){
                console.log("error - no");
              }
              

              updateDatabaseRecord(req.params.devID);
              updateDbParams(req.params.devID, obj);
            }else{
              statusCode = 0;
              var response = JSON.stringify({ status: statusCode })
              console.log("Error");
              insertLog(3, "Some info");
            }
            
            res.setHeader('Content-Type', 'application/json');
            // console.log(rows[0].Value);
            res.send(response);
        });
    } catch (err) {
        // Handle the error here.
    }
});

app.get("/dev/:devID/params",function(req,res){
  console.log(req.params.devID);
  try {
    var sqlQuery = ' ';

    connection.query('SELECT d.Status, ds.ParamID, ds.Value FROM tDevice as d RIGHT JOIN tDeviceSetting as ds ON d.DeviceID = ds.DeviceID WHERE d.SerialNum = ?',
      [req.params.devID],
      function(err, results) {
        var statusCode;
        //connection.end();
        if (!err){
          console.log('The solution is: ', results);

          var JSONString = (JSON.stringify(results));
          insertLog(1, JSONString);
          statusCode = 1;
        }else{
          console.log('Error while performing Query.');

          insertLog(2, err.errno);
          console.log(err);
          statusCode = 0;
        }

      res.setHeader('Content-Type', 'application/json');
      res.send(JSON.stringify({ status: statusCode, data: results }));
    });
  } catch (err) {
    // Handle the error here.
  }
});

app.get("/asciilogo.txt",function(req,res){
  res.setHeader('Content-Type', 'text/plain');
  //res.setHeader('Content-Length', 2263);
  res.sendFile('/var/www/iot.hub/nodejs/asciilogo.txt');
});

app.get("/json",function(req,res){
  res.setHeader('Content-Type', 'application/json');
  //res.setHeader('Content-Length', 2263);
  res.sendFile('/var/www/iot.hub/nodejs/data.json');
});

app.listen(3000);

// function for updating the devices last communication time
function updateDatabaseRecord(deviceSerial){
  var timestamp = Math.floor(new Date() / 1000);

  var query = connection.query('UPDATE tDevice SET LastComms = ? WHERE SerialNum = ?', [timestamp, deviceSerial], function(err, result) {
    // console.log(result);
    // console.log(err);
  });
}
  
function updateParam(ParamID, DeviceID, ParamValue){
    var timestamp = Math.floor(new Date() / 1000);
    var newRow  = {ParamID: ParamID, DeviceID: DeviceID, Value: ParamValue, Time: timestamp};
    
    var query = connection.query('INSERT INTO tDeviceSetting SET ? ON DUPLICATE KEY UPDATE Value = VALUES(Value), Time = VALUES(Time)', newRow, function(err, result) {
        //console.log(result);
        //console.log(err);
        //console.log(paramList.p1);
        //console.log(paramList.p2);
        //console.log(paramList.p3);
        //console.log(paramList.param4);
    });
}

function getDeviceParam(deviceID, paramID){
    try {

    } catch (err) {
        // Handle the error here.
    }
}

function updateDbParams(deviceSerial, paramList){
    try {
        var sqlQuery = ' ';
        
        connection.query('SELECT DeviceID FROM tDevice WHERE SerialNum = ? LIMIT 1',[deviceSerial],
        function(err, results) {
            var deviceID = results[0].DeviceID;
            //connection.end();
            if (!err){
                //console.log("Getting ID FOR Device Serial: ");
                //console.log(deviceSerial);
                //console.log();
                //console.log(deviceID);
                //updateParam(1, deviceID, paramList.p1);
                var somethingStupid = getServers();
                console.log(somethingStupid);
                updateParam(2, deviceID, paramList.p2);
                updateParam(3, deviceID, paramList.p3);
                
                //console.log('no errors');
                //console.log(deviceID);
                //console.log("updated");
            }else{
                console.log('Error while performing Query.');
            }
        });
    } catch (err) {
        // Handle the error here.
        console.log('Error processing request');
        console.log(err);
    }
}

function insertLog(logType, logData){
  var timestamp = Math.floor(new Date() / 1000);
  var log  = {EventType: logType, EventTime: timestamp, EventData: logData};
  var query = connection.query('INSERT INTO tLog SET ?', log, function(err, result) {
    // Neat!
    // console.log(result);
    // console.log(err);
  });
  
  // console.log(query.sql); // INSERT INTO posts SET `id` = 1, `title` = 'Hello MySQL'
}









function executeQuery(query, callback) {
pool.getConnection(function (err, connection) {
    if (err) {
        return callback(err, null);
    }
    else if (connection) {
        connection.query(query, function (err, rows, fields) {
            connection.release();
            if (err) {
                return callback(err, null);
            }
            return callback(null, rows);
        })
    }
    else {
        return callback(true, "No Connection");
    }
});
}


function getResult(query,callback) {
  executeQuery(query, function (err, rows) {
     if (!err) {
        callback(null,rows);
     }
     else {
        callback(true,err);
     }
   });
}

function getServers(){
    getResult("SELECT * FROM tDeviceSetting",function(err,rows){
        if(!err){
            console.log(rows);
            return rows;
        }else{
            console.log(err);
        }
    });
}
