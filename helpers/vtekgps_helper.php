<?php

function checkDatabaseGPS($vid = 0)
{
    $vid = intval($vid);
    $CI = & get_instance();
    $motor = $CI->load->database('mapgps', true);
    if ($motor->select('id')
        ->from('motor')
        ->where(array(
        'id' => $vid
    ))
        ->limit(1)
        ->get()
        ->row()) {
        if ($vid < 100 && $vid > 0) {

            $table = $CI->load->database('node', TRUE);

            // if( $table->query("SHOW TABLES LIKE 'data$vid'")->result() >0 ){
            if ($table->table_exists("data" . $vid)) {
                return $table;
            } else {
                $table = false;
            }
        } else
            if ($vid < 0) { // demo table
                $table = $CI->load->database('nodedemo', TRUE);
                if ($table->table_exists("data" . abs($vid))) {
                    return $table;
                }
            }
    }
    if (! isset($table))
        return FALSE;
    else
        return $table;
}

function checkOwnerGPS($vid = 0)
{
    $CI = & get_instance();

    if (checkDatabaseGPS($vid) != FALSE) {
        $motor = $CI->load->database('mapgps', true);
        if ($motor->select('*')
            ->from('motor_tracking')
            ->where(array(
            'taget' => $vid,
            'owner' => $CI->session->userdata('uid')
        ))
            ->count_all_results() > 0) {
            return TRUE;
        }
        return FALSE;
    }
    return FALSE;
}

function distance($lat1, $lng1, $lat2, $lng2, $miles = true)
{
    $dlat = ($lat2 - $lat1);
    $dlng = ($lng2 - $lng1);
    if ($dlat != 0 && $dlng != 0) {
        if ($dlat == 0) {
            bug('$dlat=' . $dlat);
        }
        if ($dlng == 0) {
            bug('$dlat=' . $dlng);
        }
        $km = sqrt($dlng * $dlng + $dlat * $dlat) * 110;
    }
    if (! isset($km) || $km == 'NAN') {
        $km = 0;
    }
    return ($miles ? ($km) : $km / 1000);
}

function distance1($lat1, $lng1, $lat2, $lng2, $miles = true)
{
    $pi80 = M_PI / 180;
    $lat1 *= $pi80;
    $lng1 *= $pi80;
    $lat2 *= $pi80;
    $lng2 *= $pi80;

    $r = 6372.797; // mean radius of Earth in km
    $dlat = $lat2 - $lat1;
    $dlng = $lng2 - $lng1;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $km = $r * $c;

    return ($miles ? ($km * 0.621371192) : $km);
}

function angle( $lat1, $lng1, $lat2, $lng2 ) {
    // Convert to radians.

//     // Compute the angle.
//     $angle = - tan( sin( $lng1 - $lng2 ) * cos( $lat2 ), cos( $lat1 ) * sin ( $lat2 ) - sin ( $lat1 ) * cos( $lat2 ) * cos( $lng1 - $lng2 ) );
//     if ( $angle < 0.0 )
//         $angle  += M_PI * 2.0;
//     if ($angle == 0) {$angle=1.5;}
//     return $angle;

    $dLon = ($lng2 - $lng1);

    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

    $brng = atan2($y, $x);

    $brng = rad2deg($brng);
    $brng = ($brng + 360) % 360;
    $brng = 360 - $brng; // count degrees counter-clockwise - remove to make clockwise

    return $brng;
}


function alphaID($in, $to_num = false, $pad_up = false, $passKey = null) {
    $index = "abcdefghijklmnoprstuvwxyzABCDEFGHIJKLMNOPRSTUVWXYZ";
    if ($passKey !== null) {
        // Although this function's purpose is to just make the
        // ID short - and not so much secure,
        // with this patch by Simon Franz (http://blog.snaky.org/)
        // you can optionally supply a password to make it harder
        // to calculate the corresponding numeric ID

        for ($n = 0; $n<strlen($index); $n++) {
            $i[] = substr( $index,$n ,1);
        }

        $passhash = hash('sha256',$passKey);
        $passhash = (strlen($passhash) < strlen($index))
        ? hash('sha512',$passKey)
        : $passhash;

        for ($n=0; $n < strlen($index); $n++) {
            $p[] =  substr($passhash, $n ,1);
        }

        array_multisort($p,  SORT_DESC, $i);
        $index = implode($i);
    }

    $base  = strlen($index);

    if ($to_num) {
        // Digital number  <<--  alphabet letter code
        $in  = strrev($in);
        $out = 0;
        $len = strlen($in) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $bcpow = bcpow($base, $len - $t);
            $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
        }

        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $out -= pow($base, $pad_up);
            }
        }
        $out = sprintf('%F', $out);
        $out = substr($out, 0, strpos($out, '.'));
    } else {
        // Digital number  -->>  alphabet letter code
        if (is_numeric($pad_up)) {
            $pad_up--;
            if ($pad_up > 0) {
                $in += pow($base, $pad_up);
            }
        }

        $out = "";
        for ($t = floor(log($in, $base)); $t >= 0; $t--) {
            $bcp = bcpow($base, $t);
            $a   = floor($in / $bcp) % $base;
            $out = $out . substr($index, $a, 1);
            $in  = $in - ($a * $bcp);
        }
        $out = strrev($out); // reverse
    }

    return $out;
}

function mortorID ($int=0, $to_num = FALSE){
    $face = 20130524;
    if($to_num === TRUE ){
        return alphaID($int,TRUE) - $face;
    } else {
        return alphaID( $face + intval($int) );
    }
}


