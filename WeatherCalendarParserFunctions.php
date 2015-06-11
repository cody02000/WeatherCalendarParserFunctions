<?php
 
class ExtWCPFHook {
	// Tell MediaWiki that the parser function exists.
	public static function register( &$parser ) {
	 
	   // Create a function hook associating the "example" magic word with the
	   // ExampleExtensionRenderParserFunction() function. See: the section 
	   // 'setFunctionHook' below for details.
	   $parser->setFunctionHook( 'temperature', 'ExtWCPF::TemperatureRender' );
	   $parser->setFunctionHook( 'sunrise', 'ExtWCPF::SunriseRender' );
	   $parser->setFunctionHook( 'sunset', 'ExtWCPF::SunsetRender' );
	 
	   // Return true so that MediaWiki continues to load extensions.
	   return true;
	}

}

class ExtWCPF {
	// Render the output of the parser function.
	public static function TemperatureRender( $parser, $temperature = '', $originalUnit = '', $mainUnit = '', $display ='') {
	 
	   // The input parameters are wikitext with templates expanded.
	   // The output should be wikitext too.
	   $output="";
		switch (strtolower($originalUnit)) {
			case 'f':
			case 'fahrenheit':
				$unit['convert']='°C';
				$unit['original']='°F';
				$temp['original']=$temperature;
				$temp['convert']=($temperature - 32) * (5/9);
				break;
			case 'c':
			case 'celsius':
				$unit['convert']='°F';
				$unit['original']='°C';
				$temp['original']=$temperature;
				$temp['convert']=($temperature * (9/5)) + 32;
				break;
			default:
				return $output="'''Incorrect usage of temperature conversion unit, please use unit F or C.'''";
		}
		$temp['convert']=round($temp['convert'],1);
		
		switch (strtolower($mainUnit)) {
			case 'f':
			case 'fahrenheit':
				$mainUnit='°F';
				break;
			case 'c':
			case 'celsius':
			default:
				$mainUnit='°C';
				break;
			
		}
		
		switch ($mainUnit) {
		case $unit['original']:
			$output = $temp['original'] . ' ' . $unit['original'] . ' (' . $temp['convert'] . ' ' . $unit['convert'] . ')';
			break;
		case $unit['convert']:
			$output = $temp['convert'] . ' ' . $unit['convert'] . ' (' . $temp['original'] . ' ' . $unit['original'] . ')';
			break;
		}
		return $output;
	}

	public static function SunriseRender( $parser, $date = '', $lat = '', $long = '', $zenith=null, $offset=null) {
		date_default_timezone_set('Europe/London');
		$offset = ($offset==null ? self::WCPFoffset($date) : $offset);
		$zenith = ($zenith==null ? 90+50/60: $zenith);
		$output=date("h:i A",date_sunrise(strtotime($date), SUNFUNCS_RET_TIMESTAMP, $lat, $long, $zenith, $offset));
		return $output;
	}

	public static function SunsetRender( $parser, $date = '', $lat = '', $long = '', $zenith=null, $offset=null) {
		date_default_timezone_set('Europe/London');
		$offset = ($offset==null ? self::WCPFoffset($date) : $offset);
		$zenith = ($zenith==null ? 90+50/60: $zenith);
		$output=date("h:i A",date_sunset(strtotime($date), SUNFUNCS_RET_TIMESTAMP, $lat, $long, $zenith, $offset));
		return $output;
	}

	public static function WCPFoffset ($date="now") {
	$time = new DateTime($date, new DateTimeZone('Europe/London'));
	$offset=$time->getOffset() / 3600;
		return $offset;
	}
}