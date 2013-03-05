<?php //Nulled by VxF.cc
class GFNCoders_XenSocialize_Helper_UrlShortener
{
	const LOGIN = "\u006D\u0072\u0067\u006F\u006F\u0064\u0069\u0065\u0032\u0073\u0068\u006F\u0065\u0073";
	const SECRET = "\u0052\u005F\u0062\u0065\u0039\u0063\u0034\u0037\u0064\u0030\u0066\u0030\u0065\u0036\u0062\u0065\u0032\u0065\u0066\u0038\u0061\u0039\u0037\u0031\u0030\u0065\u0064\u0030\u0064\u0036\u0036\u0038\u0036\u0030";
	
	private function __construct() { }
	
	protected static $_host = 'http://xens.co/';
	
	public static function shorten($longUrl)
	{
		try{
			$client = XenForo_Helper_Http::getClient('https://api-ssl.bitly.com/shorten');
			$client->setParameterGet(array(
					'longUrl' => $longUrl,
					'format' => 'json',
					'login' => json_decode('{"t":"' . self::LOGIN . '"}')->{'t'},
					'apiKey' => json_decode('{"t":"' . self::SECRET . '"}')->{'t'}
				));
			
			$return = json_decode($client->request()->getBody(), true);
			if(empty($return['errorMessage']))
				return $return;
			else
				return false;
		} catch(Exception $e) { XenForo_Error::logException($e); return false; }
	}
	
	public static function fetchId($longUrl)
	{
		if($return = self::shorten($longUrl))
			return $return['results'][$longUrl]['userHash'];
		else
			return false;
	}
	
	public static function getShortenedUrlForShare($longUrl)
	{
		if(strpos($longUrl, '/localhost/') !== false)
			return $longUrl;
		
		if($return = self::fetchId($longUrl))
			return self::$_host . $return;
		else
			return $longUrl;
	}
}