<?php

/*------------------------------------------------------------------------------
** Version:       1.2
** Author:        Boris Baumann
** Email & URL:   boris.baumann@gmail.com
------------------------------------------------------------------------------ */

function coord_converter ( $pattern, $nate , $parameter = false) {

    $patternchars =  implode( '|', str_split( 'dmsflo' ) );

    $prefixarr = Array(
        '' => Array( 'H' => 'N', 'h' => 'E', 'P' => '+', 'p' => '' ),
        '-' => Array( 'H' => 'S', 'h' => 'W', 'P' => '-', 'p' => '-' )
    );

    if (!empty($parameter['H'])) {
        $prefixarr['']['H'] = $parameter['H'][0] ;
        $prefixarr['']['h'] = $parameter['H'][1] ;
        $prefixarr['-']['H'] = $parameter['H'][2] ;
        $prefixarr['-']['h'] = $parameter['H'][3] ;
    }

    // find all Base-Patterns
    // $y = preg_match_all( "/($patternchars)\\1*/i", $pattern, $prehit );

$new_string = $pattern;

    if (preg_match_all( "/($patternchars)\\1*/i", $pattern, $prehit )) {
    
    // echo( '<xmp>'.print_r( $prehit, true ).'</xmp>' );
    // echo( '<xmp>' . $y . '</xmp>' );

    //Reconstruct Floatpatterns
    for ( $i = 0 ; $i < count( $prehit[0] ) - 1 ; $i++ ) {
        $floatpattern = $prehit[0][$i].'\.'.$prehit[0][$i + 1];
        if ( preg_match( "/$floatpattern/i", $pattern, $fp ) ) $floatpattern_arr[$i + 1] = $fp[0];
    }

    // Build Full array with 0: int & Floatpattern, 1: Integerpart, 2: Floatpart
    foreach ( $prehit[0] as $k => $v ) {
        if ( empty( $floatpattern_arr[$k] ) ) {
            $hit[0][$k] = empty( $floatpattern_arr[$k + 1] ) ?  $v : $floatpattern_arr[$k + 1];
            $hit[1][$k] = $v;
            $hit[2][$k] = '';
        } else {
            $hit[0][$k] = '';
            $hit[1][$k] = '';
            $hit[2][$k - 1] = $v;
        }
    }

    foreach ( $hit[0] as $k => $v ) {
        if ( !empty( $v ) ) {
            $i = $hit[0][$k];
            $digits[$i]['intpresentation'] = preg_match( '~^\p{Lu}~u', $hit[1][$k][0] ) ? 'e' : 'm' ;
            $digits[$i]['floatpresentation'] = preg_match( '~^\p{Lu}~u', $hit[2][$k][0] ) ? 'e' : 'm' ;
            $digits[$i]['intkind'] = $hit[1][$k][0] ;
            $digits[$i]['floatkind'] = $hit[2][$k][0];
            $digits[$i]['int'] = strlen( $hit[1][$k] );
            $digits[$i]['float'] = strlen( $hit[2][$k] );
        }
    }

    // convert $nate to decimal coordinate
    $matches = array_map( function ( $str ) {
        return preg_match( '#[0-9]{1,}\.?[0-9]{0,}#', $str, $v ) ? $v[0] : 0;
    }
    , // ^ clean segments to float
    preg_split( "/[°'\"\s]+/",  // <- split the nate into segments
    preg_replace( '/, /', '.', $nate ) // <-  change ',' to '.' 
    ) 
  );  

  $prefix = preg_match ( '#(w|s|-)#i', $nate, $v ) ? '-' : '';
  $autohemisphere = preg_match ( '#(n|s)#i', $nate, $v ) ? 'ns' : 'we';
  $natedez = ( count( $matches ) > 1 ) ? $prefix . ( $matches[0] + ( $matches[1] * 60 + $matches[2] ) / 3600 ) : $prefix.$matches[0];

  // echo($autohemisphere."<br>");

  // $prefixarr['']['A'] = "xxx";
  // $prefixarr['']['a'] = "xxx";

  // echo("<xmp>".print_r($prefixarr,true)."</xmp>");
  
// define d=degrees,m=minute,s=second; r=raw/fullfloat,i=integerpart,f=floatpart
// to use this segments later  
  $calc['d']['i'] = abs(intval($natedez));
  $calc['d']['r'] = abs($natedez);
  $calc['d']['f'] = preg_match('/\d*\.(\d*)/',$calc['d']['r'],$v) ? $v[1] : '0' ;
  
  $calc['m']['r'] = (abs($natedez) -$calc['d']['i']) * 60;
  $calc['m']['i'] = intval($calc['m']['r']);
  $calc['m']['f'] = preg_match('/\d*\.(\d*)/',$calc['m']['r'],$v) ? $v[1] : '0' ;

  $calc['s']['r'] = ($calc['m']['r'] - $calc['m']['i']) * 60;
  $calc['s']['i'] = intval($calc['s']['r']);
  $calc['s']['f'] = preg_match('/\d*\.(\d*)/',$calc['s']['r'],$v) ? $v[1] : '0' ;

  $calc['f'] = $calc['d'];
  $calc['l'] = $calc['m'];
  $calc['o'] = $calc['s'];

foreach ($digits as $upattern => &$v) {

    $kind = strtolower($v['intkind']);

    if ($kind == 'f' || $kind == 'l' || $kind == 'o' ) {
        $v['floatkind'] = $v['intkind'];  
        $v['float'] = $v['int'];
        $v['intkind'] = '';  
        $v['int'] = 0;  
    }

    $digits[$upattern]['raw'] = $calc[$kind]['r'] ;
    
    $int_new = ($v['intpresentation'] == 'e') ? str_pad($calc[$kind]['i'], $v['int'], "0", STR_PAD_LEFT) : $calc[$kind]['i'];
    
    $float_new = ($v['floatpresentation'] == 'e') ? 
    str_pad(explode('.',round($calc[$kind]['r'],$v['float']))[1], $v['float'], "0", STR_PAD_RIGHT) : 
    explode('.',round($calc[$kind]['r'],$v['float']))[1];

    $digits[$upattern]['presetation'] = $int_new . ((!empty($float_new)) ? '.' . $float_new : '') ;

    if ($v['floatkind'] == 'F' || $v['floatkind'] == 'L' || $v['floatkind'] == 'O' ) {
        $digits[$upattern]['presetation'] = str_pad(explode('.',round($calc[$kind]['r'],$v['float']))[1], $v['float'], "0", STR_PAD_RIGHT) ;
    } 
    if ($v['floatkind'] == 'f' || $v['floatkind'] == 'l' || $v['floatkind'] == 'o' ) {
        $digits[$upattern]['presetation'] = explode('.',round($calc[$kind]['r'],$v['float']))[1];
    } 
    
    $replacearr[$upattern] =  $digits[$upattern]['presetation'];
}

$new_string = strtr($pattern,$replacearr);

} // end if $pattern contains numbers

$new_string = strtr($new_string,$prefixarr[$prefix]);



    return $new_string;
}

?>