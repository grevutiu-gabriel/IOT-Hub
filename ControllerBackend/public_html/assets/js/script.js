$(document).on("pagehide", function(event, ui){
    $(event.target).remove();
  });

$(document).on('pageshow', '#config', function(e, data){ 
    // alert($('#basic').val());
    // $( '#basic' ).tooltip();
});

$(document).on('pageshow', '#devices', function(e, data){
    $( "#lighting-popup" ).popup();
    $( "#power-popup" ).popup();
    $( "#climate-popup" ).popup();
    $( "#media-popup" ).popup();
    $( "#remote-popup" ).popup();

    // Get the URL parameters
    var device_url = '/api/devices';
    var url_vars = getUrlVars();
    // alert(url_vars);
    if("roomid" in url_vars){ // if room ID exists in the URL
        device_url = '/api/devices/roomid/'+url_vars.roomid;
    }
    $('#device-list').empty();
    
    // Get JSON data from api/devices
    $.ajax({url: device_url,
        dataType: "jsonp",
        async: true,
        success: function (result) {
            $.each( result, function(i, row) {
                var switchClass = (row.relay_state == 0 ? "onSwitch" : "offSwitch");
                var switchAction = (row.relay_state == 0 ? 1 : 0);
                var deviceType;
                var temperature;
                var tempDisplay = '';

                $.each( row.parameters, function(j, param) {
                    if(param.Key === 'currentTemp'){
                        temperature = param.Value;
                    }
                });
                // console.log(row.parameters);
                // console.log(row.TypeName);
                switch(row.TypeName) {
                    case 'Lighting':
                        deviceType = "lighting";
                        break;
                    case 'Climate':
                        deviceType = "climate";
                        tempDisplay = '<p class="ui-li-aside">'+ temperature +'&deg;C</p>';
                        break;
                    case 'Smart Plug':
                        deviceType = "power";
                        break;
                    case 'Media Player':
                        deviceType = "media";
                        break;
                    case 'Smart TV':
                        deviceType = "remote";
                        break;
                }
                var htmlRow = `
                    <li>
                        <div class="spinner">
                            <div class="bounce1"></div>
                            <div class="bounce2"></div>
                            <div class="bounce3"></div>
                        </div>
                        <a href-"#" class="device-control" data-type="${deviceType}" data-id="${row.ID}" >
                            <img src="/assets/img/${row.Path}" class="device-icon" alt="Icon">
                            <h2>${row.Name}</h2><p>${row.IP}</p>
                            ${tempDisplay}
                        </a>
                        <a href="#" class="device-power-btn ${switchClass}" id="dsw-${row.ID}" data-id="${row.ID}" data-action="${switchAction}">Power</a>
                    </li>
                `;
                $('#device-list').append(htmlRow);
            });
            $('#device-list').listview('refresh');
        },
        error: function (request,error) {
            alert('Network error has occurred please try again!');
        }
    });         
});

$(document).on('pagebeforeshow', '#rooms', function(e, data){  
    $('#room-list').empty();
    // Get JSON data from api/devices
    $.ajax({url: '/api/rooms',
        dataType: "jsonp",
        async: true,
        success: function (result) {
            $.each( result, function(i, row) {
                var htmlRow = `
                    <li>
                        <a href="/devices?roomid=${row.ID}" data-id="${row.ID}" >
                            <img src="/assets/img/${row.Path}" class="device-list-img" alt="Icon">
                            <h2>${row.Name}</h2>
                            <p>Devices: ${row.DeviceCount}</p>
                        </a>
                    </li>
                `;
                $('#room-list').append(htmlRow);
            });
            $('#room-list').listview('refresh');
        },
        error: function (request,error) {
            alert('Network error has occurred please try again!');
        }
    });         
});

$(document).on('vclick', '.device-power-btn', function(e){
    // Check if the anchor button is enabled
    if (!$(this).data('disabled')){
        // Disable the anchor button
        $(this).data('disabled',true);

        // Prevent default event triggering
        e.preventDefault();

        // Show the loading spinner
        $(this).parent().children(".spinner").css("visibility", "visible");

        var deviceID = $(this).attr("data-id");
        var action = $(this).attr("data-action");
        var device_commands = {power:{state: action}};
        var command = {id:deviceID, device_commands};

        $.post("/api/device/id/"+deviceID, { "device": command }, function( data, status, xhr ) {
            //success
            if(!data.response.error){
                if(data.command.device_commands.power.state == 0){
                    $("#dsw-"+data.command.id).removeClass( "offSwitch" ).addClass( "onSwitch" );
                    $("#dsw-"+data.command.id).attr( "data-action", 1 );
                }else{
                    $("#dsw-"+data.command.id).removeClass( "onSwitch" ).addClass( "offSwitch" );
                    $("#dsw-"+data.command.id).attr( "data-action", 0 );
                }
            }else{
                toast("Device Error");
            }
        })

        .always(function(data) {
            // Re-enable the anchor button after AJAX post complete
            $("#dsw-"+data.command.id).data('disabled',false);

            // Hide the loading spinner
            $("#dsw-"+data.command.id).parent().children(".spinner").css("visibility", "hidden");
        });

    } // end anchor button check
});

$(document).on('vclick', '.device-control', function(e){
    // console.log($(this).attr('data-type'));
    var deviceID = $(this).attr('data-id');

    switch($(this).attr('data-type')) {
        case 'lighting':
            $( "#lighting-popup" ).popup( "open" );
            break;
        case 'climate':
            // Load the thermostat widget into the climate div
            $("#climate-popup").load( "/widgets/thermostat", function() {
                // Get JSON data from api/devices
                $.ajax({url: '/api/device/id/'+deviceID,
                    dataType: "jsonp",
                    async: true,
                    success: function (result) {
                        console.log(result);
                        // Bind the slider functionality to the climate page
                        $( "#temp-slider" ).bind( "change", function(event, ui) {
                            $('#set-temp').text($(this).val()+String.fromCharCode(8451));
                            $('#set-temp').attr("data-temp", $(this).val());
                        });

                        $.each( result[0].parameters, function(i, row) {
                            console.log(row);
                            switch(row.Key){
                                case 'setTemp':
                                    $('#set-temp').text(row.Value+String.fromCharCode(8451));
                                    $('#temp-slider').val(row.Value);
                                    break;
                                case 'currentTemp':
                                    $('#current-temp').text(row.Value+String.fromCharCode(8451));
                                    break
                            }
                        });
                        
                        // Open the popup window
                        $( "#climate-popup" ).popup( "open" );
                    },
                    error: function (request,error) {
                        alert('Network error has occurred please try again!');
                    }
                });

                $('#save-temperature').attr("data-id", deviceID);
            });
            break;
        case 'power':
            $("#power-popup").load( "/widgets/power_control/"+$(this).data('id'), function() {
                $( "#power-popup" ).popup( "open" );
            });
            break;
        case 'media':
            $("#media-popup").load( "/widgets/media_player", function() {
                $( "#media-popup" ).popup( "open" );
            });
            break;
        case 'remote':
            // Load the remote control widget into the remote div
            $("#remote-popup").load( "/widgets/remote_control", function() {
                // Get JSON data from api/devices
                $.ajax({url: '/api/device/id/'+deviceID,
                    dataType: "jsonp",
                    async: true,
                    success: function (result) {
                        $.each( result[0].parameters, function(i, row) {
                            // console.log(row);
                            switch(row.Key){
                                case 'volume':
                                    $('.vol').text('Volume - '+row.Value);
                                    break;
                            }
                        });

                        // Open the popup window
                        // $( "#climate-popup" ).popup( "open" );
                    },
                    error: function (request,error) {
                        alert('Network error has occurred please try again!');
                    }
                });

                // Open the popup window
                $( "#remote-popup" ).popup( "open" );
                $('.btn').attr("data-id", deviceID);
            });
            break;
    }
});

$(document).on('vclick', '#save-temperature', function(e){
    // console.log("Save Temperature: "+$("#set-temp").attr("data-temp")+" to ID: "+$(this).attr("data-id"));

    var deviceID = $(this).attr("data-id");
    var setTemp = $("#set-temp").attr("data-temp");
    var device_commands = {climate:{temperature: setTemp}};
    var command = {id:deviceID, device_commands};

    $.post("/api/device/id/"+deviceID, { "device": command }, function( data, status, xhr ) {
        // console.log(data);
        $( "#climate-popup" ).popup( "close" );
        //success
        if(!data.response.error){
            // if(data.command.device_commands.power.state == 0){
            //     $("#dsw-"+data.command.id).removeClass( "offSwitch" ).addClass( "onSwitch" );
            //     $("#dsw-"+data.command.id).attr( "data-action", 1 );
            // }else{
            //     $("#dsw-"+data.command.id).removeClass( "onSwitch" ).addClass( "offSwitch" );
            //     $("#dsw-"+data.command.id).attr( "data-action", 0 );
            // }
        }else{
            toast("Device Error");
        }
    })

    .always(function(data) {
        // Re-enable the anchor button after AJAX post complete
        // $("#dsw-"+data.command.id).data('disabled',false);

        // Hide the loading spinner
        // $("#dsw-"+data.command.id).parent().children(".spinner").css("visibility", "hidden");
    });
});

$(document).on('vclick', '#btn-login', function(event){
    var loginForm =
        '<ul>' +
            '<li><label for="txt-user">Username</label><input type="text" name="txt-user" id="txt-user" placeholder="Enter username..."/></li>' +
            '<li><label for="txt-pass">Password</label><input type="password" name="txt-pass" id="txt-pass" placeholder="Enter password..."/></li>' +
            '<fieldset data-role="controlgroup" data-iconpos="right">' +
                '<input name="chk-remember" id="chk-remember" type="checkbox" />' +
                '<label for="chk-remember">Remember Me</label>' +
            '</fieldset>' +
            '<a href="#" id="btn-login-submit" class="ui-btn ui-btn-b ui-corner-all">Login</a>' +
        '</ul>';
    $('#login-form').empty();
    $( loginForm ).appendTo( "#login-form" ).enhanceWithin();
});

$(document).on('vclick', '#btn-login-submit', function(){
    $.mobile.loading( 'show', {});
    var username = $('#txt-user').val();
    var password = $('#txt-pass').val();
    var jqxhr = $.post( "api/login", {user: username, pass: password}, function(data) {
        if(data.status === 'success'){
            document.location.href = '/'
        }else{
            toast("Invalid Credentials");
        }
    })

    .fail(function() {
        // alert( "error" );
        toast("error logging in");
    })

    .always(function() {
        // alert( "finished" );
        $.mobile.loading( 'hide', {});
    });
});

$(document).on('vclick', '#btn-logout', function(){
    var jqxhr = $.get( "api/logout", function(data) {
        // alert( "success" );
        document.location.href = '/';
    })

    .always(function() {
        // alert( "finished" );
    });
});

$(document).on('vclick', '.remote-control .btn', function(e){
    e.preventDefault();
    console.log($(this).data("id"));
    var deviceID = $(this).data("id");
    var action = $(this).data("action");

    var device_commands = {"action": action};
    var command = {id:deviceID, device_commands};
    $.post("/api/device/id/"+deviceID, { "device": command }, function( data, status, xhr ) {
        // alert( "success" );
        // $(this).blur();
        // $("ipModal").modal("hide");
    })

    .always(function() {
        // alert( "finished" );
    });
});

function refreshDeviceList()
{
    $('#device-list').empty();
    // Get the device data from the API
    $.get( "api/devices", function(data) {
        // Iterate over the response array
        $.each(data, function(i, row) {
            var switchClass = (row.relay_state == 0 ? "onSwitch" : "offSwitch");
            var switchAction = (row.relay_state == 0 ? 1 : 0);
            var deviceRow = `
                <li>
                    <div class="spinner">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                    <a href="#device-info-dialog" data-rel="popup" data-position-to="window" data-transition="pop" data-id="${row.ID}" >
                        <img src="/assets/img/${row.Path}" class="device-icon" alt="Icon">
                        <h2>${row.Name}</h2><p>${row.IP}</p>
                        <p class="ui-li-aside">24.45&deg;C</p>
                    </a>
                    <a href="#" class="device-power-btn ${switchClass}" id="dsw-${row.ID}" data-id="${row.ID}" data-action="${switchAction}">Power</a>
                </li>
            `;
            $('#device-list').append(deviceRow);
        });
    })

    .always(function() {
        // Refresh the ListView
        $('#device-list').listview('refresh');
    }); 
}

function getUrlVars() {
    var vars = [], hash;
    var href = window.location.href;
    var queryUrl =href.slice(href.lastIndexOf( '?' ) + 1);
    var hashes = queryUrl.split( '&' );
    for ( var i=0; i<hashes.length; i++) {
        hash = hashes[i].split( '=' );
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
} 

function toast(message) {
    var $toast = $('<div class="ui-loader ui-overlay-shadow ui-body-e ui-corner-all"><h3>' + message + '</h3></div>');

    $toast.css({
        display: 'block', 
        background: '#fff',
        opacity: 0.90, 
        position: 'fixed',
        padding: '7px',
        'text-align': 'center',
        width: '270px',
        left: ($(window).width() - 284) / 2,
        top: $(window).height() / 2 - 20
    });

    var removeToast = function(){
        $(this).remove();
    };

    $toast.click(removeToast);

    $toast.appendTo($.mobile.pageContainer).delay(2000);
    $toast.fadeOut(400, removeToast);
}
