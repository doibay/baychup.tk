<?php

/**
 * Imgur Uploader, used to upload an image to imgur.com
 * 
 * @project		Image Uploader
 * @class		Imgur Uploader
 * @author		Phan Thanh Cong <chiplove.9xpro@gmail.com>
 * @since		November 8, 2011
 * @version		1.1
 * @since		March 7, 2012
 * @copyright	chiplove.9xpro
*/

if(!class_exists('c_Image_Uploader'))
{
	die('c_Image_Uploader must be required');
}
class c_Image_Uploader_Imgur extends c_Image_Uploader
{
	// set plugin name
	public $plugin_name = 'imgur';
	
	
	// comming son
	public $api_key;
	
	public function set_api($api_key)
	{
		$this->api_key = $api_key;
	}
	
	public function _login($username, $password)
	{
		//comming son
		return FALSE;
	}
	
	public function transload($url)
	{
		return $this->_send_data(array('url' => $url));
	}

	
	public function upload($filePath)
	{
		return $this->_send_data(array('file' => '@' . $filePath));
	}

	/**
	 * Send file or url to imgur
	*/
	protected function _send_data(array $params)
	{
		$this->http->clear();
		$this->http->useCurl(false);
		//get cookie & sid_hash
		if(!$this->session('uploaded') OR $this->session('uploaded') > 20)
		{
			$this->http->execute('http://imgur.com/?noFlash');	
			// lưu cookie
			$this->session('cookie', implode(';', $this->http->_headers['set-cookie']));
			//sid_hash
			preg_match('#sid_hash\s+?=\s+?\'([^\']+)\'#i', $this->http->getResult(), $m);

			$this->session('sid_hash', $m[1]);
			$this->session('uploaded', 1);
		}
		$this->session('uploaded', $this->session('uploaded') + 1);
		
		//begin upload process
		$this->http->clear();
		$this->http->useCurl(false);
		$this->http->setCookie( $this->session('cookie'));
		$this->http->setSubmitMultipart();
		$this->http->setHeader(array(
			'Referer: http://imgur.com/?noFlash'
		));
		$this->http->setParams(array(
			'album_title' => 'Optional Album Title',
			'layout' => 'b',
		));
		$this->http->setParams($params);
		$this->http->execute('http://imgur.com/upload?sid_hash=' . $this->session('sid_hash'));
	
		$json = $this->http->getResult();
		$result = json_decode($json, true);

		if(@$result['hash'])
		{
			$mime = c_Http::mimeType($filePath);
			switch(substr($mime, -3))
			{
				case 'bmp': 
				case 'png':
					$extension = 'png'; 
					break;
				case 'gif':
					$extension = 'gif'; 
					break;	
				case 'peg': 
				case 'jpg':
				default:
					$extension = 'jpg'; 
					break;
			}
			$url = 'http://i.imgur.com/' . $result['hash'] . '.' . $extension;
			return $url;
		}
		else
		{
			return __METHOD__ . ': Errors!';
		}
	}	
}
