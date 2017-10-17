<?php

$commands =
        [
                'info'     => '{"system":{"get_sysinfo":{}}}',
                'on'       => '{"system":{"set_relay_state":{"state":1}}}',
                'off'      => '{"system":{"set_relay_state":{"state":0}}}',
                'cloudinfo'=> '{"cnCloud":{"get_info":{}}}',
                'wlanscan' => '{"netif":{"get_scaninfo":{"refresh":0}}}',
                'time'     => '{"time":{"get_time":{}}}',
                'schedule' => '{"schedule":{"get_rules":{}}}',
                'countdown'=> '{"count_down":{"get_rules":{}}}',
                'antitheft'=> '{"anti_theft":{"get_rules":{}}}',
                'reboot'   => '{"system":{"reboot":{"delay":1}}}',
                'reset'    => '{"system":{"reset":{"delay":1}}}',
                'setcloud' => '{"cnCloud":{"set_server_url":{"server":"devs.teamtechuk.com"}}}',
                'udpbc'    => '{"system":{"get_sysinfo":{}}}', //udp broadcast packet
                'ledoff'   => '{"system":{"set_led_off":{"off":1}}}',
                'ledon'    => '{"system":{"set_led_off":{"off":0}}}',
                'respond'  => '{"system":{"get_sysinfo":{"err_code":0,"sw_ver":"1.0.10 Build 160316 Rel.181342","hw_ver":"1.0","type":"IOT.SMARTPLUGSWITCH","model":"HS110(UK)","mac":"50:C7:BF:30:D1:A2","deviceId":"8006C724DA1BD539BAD6D582E897CAC21815891C","hwId":"2448AB56FB7E126DE5CF876F84C6DEB5","fwId":"9176FB9731E6D84BD775BCF6BBD742EF","oemId":"90AEEA7AECBF1A879FCA3C104C58C4D8","alias":"Corner Lamp","dev_name":"Wi-Fi Smart Plug With Energy Monitoring","icon_hash":"","relay_state":0,"on_time":0,"active_mode":"schedule","feature":"TIM:ENE","updating":0,"rssi":-56,"led_off":0,"latitude":50.863796,"longitude":0.580489}}}',
                'startup'  => '{"smartlife.iot.common.cloud":{"get_info":{}},"system":{"get_sysinfo":{}},"emeter":{"get_realtime":{}},"cnCloud":{"get_info":{}}}'
        ];

function encrypt($string){
    $key = 171;
    $result = "";

    for($i=0; $i<strlen($string); $i++){
        // XOR $key with decimal value of $string[$i]
        $a = $key ^ ord($string[$i]);
        $key = $a;
        $result = $result.chr($a);
    }
    return $result;
}

function decrypt($string){
    $key = 171;
    $result = "";
    for($i=0; $i<strlen($string); $i++){
        $a = $key ^ ord($string[$i]);
        $key = ord($string[$i]);
        $result = $result.chr($a);
    }
    return $result;
}

function broadcastUDP($bc_addr='172.24.1.255', $bc_port=9999)
{
        global $commands;
        
        //Reduce errors
        error_reporting(~E_WARNING);
        
        $port = 9999;
        $str = encrypt($commands['udpbc']);

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); 
        socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>2, "usec"=>0));
        socket_sendto($sock, $str, strlen($str), 0, $bc_addr, $port);

        $i = 0;
        $clients = array();
        while(true) {
                $ret = @socket_recvfrom($sock, $buf,2048, 0, $ip, $port);
                if($ret === false) break;
                $clients[$i]['data'] = json_decode(decrypt($buf),TRUE);
                $clients[$i]['ip'] = $ip;
                $i++;
        }

        socket_close($sock);
        
        return $clients;
}



print_r(broadcastUDP());

