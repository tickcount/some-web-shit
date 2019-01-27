<?
    $serverInfo = getServerInfo("46.174.50.11", 27231, 3);
    if($serverInfo->error) exit($serverInfo->error);

    echo '
        > Server informating <br>
        > HostName: '.$serverInfo->name.' <br>
        > Valve Anti-Cheat: '.$serverInfo->vac.' <br>
        > Current Map: '.$serverInfo->map.' <br>
        > Players & MaxPlayers: '.$serverInfo->players.' / '.$serverInfo->maxplayers.' <br>
        > Game Name: '.$serverInfo->game->name;

    function getServerInfo($ip, $port, $timeout){
        $sockCreate = socket_create(AF_INET, SOCK_DGRAM, 0);
        $sockConnection = socket_connect($sockCreate, $ip, $port);
        $sockData = "\xFF\xFF\xFF\xFF\x54\x53\x6F\x75\x72\x63\x65\x20\x45\x6E\x67\x69\x6E\x65\x20\x51\x75\x65\x72\x79\x00";

        socket_set_option($sockCreate, SOL_SOCKET, SO_RCVTIMEO, ["sec" => $timeout, "usec" => 0]);
        socket_write($sockCreate, $sockData, strlen($sockData));

        $out = socket_read($sockCreate, 4096);
        $request = explode("\x00", substr($out, 6), 5);

        if($request[4] == null) return (object) ["error" => "Can't reach server"];

        return (object) [
            "name" => $request[0],
            "map" => $request[1],
            "game" => (object) ["name" => $request[2], "desc" => $request[3]],
            "packet" => $request[4],
            "app_id" => array_pop(unpack("S", substr($request[4], 0, 2))),
            "players" => ord(substr($request[4], 2, 1)),
            "maxplayers" => ord(substr($request[4], 3, 1)),
            "bots" => ord(substr($request[4], 4, 1)),
            "dedicated" => substr($request[4], 5, 1),
            "os" => substr($request[4], 6, 1),
            "pass" => ord(substr($request[4], 7, 1)),
            "vac" => (ord(substr($request[4], 8, 1)) ? "Secured" : "Not secured")
        ];
    }