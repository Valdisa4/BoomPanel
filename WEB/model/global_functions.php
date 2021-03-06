<?php

if(!isset($db)) die();

function checkIfSteamIDExists($steamid)
{
    $url = "http://steamcommunity.com/profiles/{$steamid}/?xml=1";
    $xmlinfo = simplexml_load_file($url);
    $steamid = $xmlinfo->steamID64;
    if($steamid)
        return true;

    return false;
}

function toCommunityID($id) {
    if (preg_match('/^STEAM_/', $id)) {
        $parts = explode(':', $id);
        return bcadd(bcadd(bcmul($parts[2], '2'), '76561197960265728'), $parts[1]);
    } elseif (is_numeric($id) && strlen($id) < 16) {
        return bcadd($id, '76561197960265728');
    } else {
        return $id; // We have no idea what this is, so just return it.
    }
}


function getSteamID($result)
{

    $result = htmlspecialchars(toCommunityID($result));

    if(is_numeric($result))
    {

        if(checkIfSteamIDExists($result))
        {
            $steamid = $result;
            return ((string)($steamid));
        }

    } else {

        if(!filter_var($result, FILTER_VALIDATE_URL) === false)
        {

            $parts = parse_url($result);
            if(isset($parts['path']))
            {
                $path_parts=explode('/', $parts['path']);
                if(isset($path_parts[2]))
                {

                    if(is_numeric($path_parts[2]))
                        $url = "http://steamcommunity.com/profiles/{$path_parts[2]}/?xml=1";
                    else
                        $url = "http://steamcommunity.com/id/{$path_parts[2]}/?xml=1";

                    $xmlinfo = simplexml_load_file($url);
                    if($xmlinfo) {
                        $steamid = $xmlinfo->steamID64;
                        return ((string)($steamid));
                    }
                }
            } else {
                return -1;
            }

        } else {
            $url = "http://steamcommunity.com/id/".preg_replace('/%2F/', '-', urlencode($result) )."/?xml=1";
            str_replace("%2F", "/", $url);
            $xmlinfo = simplexml_load_file($url);
            $steamid = $xmlinfo->steamID64;
            if($steamid) {
                return ((string)($steamid));
            }
        }

    }

    return -1;
}

function GetPlayerData($steamid)
{
    $parsed = json_decode(file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='.APIKEY.'&steamids='.$steamid));
    foreach($parsed->response->players as $player){
        $data['avatar']     = $player->avatar;
        $data['username']   = $player->personaname;
        return $data;
    }
    return -1;
}

function GetPlayersAvatars($steamids)
{
    $url = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='.APIKEY.'&steamids=';
    foreach ($steamids as $steamid)
        $url .= $steamid.",%20";

    $parsed = json_decode(file_get_contents($url));
    foreach($parsed->response->players as $player) {
        $data[$player->steamid] = $player->avatar;
        //Add also username to get usernames if they are not in database
        $data["username-".$player->steamid] = $player->personaname;
    }

    if(isset($data))
        return $data;
    else
        return -1;
}


function toSteamID($id) {
    if (is_numeric($id) && strlen($id) >= 16) {
        $z = bcdiv(bcsub($id, '76561197960265728'), '2');
    } elseif (is_numeric($id)) {
        $z = bcdiv($id, '2'); // Actually new User ID format
    } else {
        return $id; // We have no idea what this is, so just return it.
    }
    $y = bcmod($id, '2');
    return 'STEAM_1:' . $y . ':' . floor($z);
}

function convertToHoursMinsBans($time, $allowzero = false)
{
    if (!$allowzero && (empty($time) || $time < 1))
        return PERMANENET;
    else if($allowzero && (empty($time) || $time < 1))
        return '0m';

    $format = null;
    $params = array();

    $days       = floor($time / 1440);
    if($days > 0) {$format .= "%01dd ";array_push($params, $days);};

    $hours      = floor($time / 60);
    if($hours > 0 && ($hours % 24) != 0) {$format .= "%01dh ";array_push($params, $hours - ($days * 24));};

    $minutes    = ($time % 60);
    if($minutes > 0) {$format .= "%01dm";array_push($params, $minutes);};

    if($format != null)
        return vsprintf($format, $params);
    else
        return '-';
}

function ShowValue($value, $defvalue = "", $int = false)
{
    if(!$int)
        return isset($value) ? htmlspecialchars($value) : $defvalue;
    else
        return isset($value) ? intval($value) : $defvalue;
}

function UP($text)
{
    return ucfirst($text);
}

function echo_dev($data)
{
    if(DEVELOPERMOD == 1) {

        if(!is_array($data)) {
            echo '<div class="container-fluid"><div class="row-fluid devcss"><div class="span12"><div class="widget-box devcss"><div class="alert alert-info alert-block">' . $data . '</div></div></div></div></div>';
        } else {
            echo '<div class="container-fluid"><div class="row-fluid devcss"><div class="span12"><div class="widget-box devcss"><div class="alert alert-info alert-block">';
            print_r($data);
            echo '</div></div></div></div></div>';
        }

    }

}

function CommandToPlayer($Query, $pid, $command)
{
    $db = new DataBase();

    //Check in which server player is online *UPDATE SHOULD GET LATEST SERVER ONLINE*
    $online = $db->selectOne("
        SELECT `steamid`, `ip`, `port`, `rcon_pw` FROM bp_players_online o 
        LEFT JOIN bp_servers s ON o.sid = s.id 
        LEFT JOIN bp_players p ON o.pid = p.id 
        WHERE pid = :pid AND connected = disconnected 
        ORDER BY connected DESC LIMIT 1
    ", array("pid" => intval($pid)));

    if($online) {

        try
        {
            $Query->Connect( $online['ip'], $online['port'], 1, 1, 1 );
            $Query->SetRconPassword($online['rcon_pw']);

            //Execute command
            $command = str_replace("{STEAMID}", toSteamID($online['steamid']), $command);
            $Query->Rcon($command);
        }
        catch( Exception $e ) { $Exception = $e; echo $e;}
        finally { $Query->Disconnect(); }

    }
}


?>
