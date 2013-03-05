<?php

/**
 * AnhSo Uploader, used to upload an image to anhso.net
 * 
 * @project		Image Uploader
 * @class		AnhSo Uploader
 * @author		Phan Thanh Cong <chiplove.9xpro@gmail.com>
 * @version		1.0
 * @since		November 7, 2011
 * @copyright	chiplove.9xpro
*/

if(!class_exists('c_Image_Uploader'))
{
	die('c_Image_Uploader must be required');
}

die('This plugin is not working for now.');

class c_Image_Uploader_AnhSo extends c_Image_Uploader
{
	public $plugin_name = 'anhso';

	public function _login($username, $password)
	{
		//coming son
	}
	
	public function upload($filePath)
	{
		if(!$this->session('uploaded') OR $this->session('uploaded') > 10)
		{
			//get bkid 
			$this->http->clear();
			$this->http->execute('http://up.anhso.net/');
			preg_match('#upload\.ashx\?bkid=([^"]+)#i', $this->http->getResult(), $m);
			
			$this->session('bkid', $m[1]);
			$this->session('uploaded', 1);
		}
		$this->session('uploaded', $this->session('uploaded') + 1);
		
		$this->http->clear();
		$this->http->setMethod('POST');
		$this->http->setSubmitMultipart();
		$this->http->setParams(array(
			'name' => 'Upload',
			'Filedata' => '@' . $filePath
		));
		$this->http->setTarget('http://up.anhso.net/upload.ashx?bkid=' . $this->session('bkid') );
		$this->http->execute();
		$json = $this->http->getResult();
		$result = json_decode(str_replace("'", '"', $json), true);	
		
		if(!$result['error'] AND $result['filename'])
		{
			return 'http://farm3.anhso.net/upload/'.$result['path'].'o/'.$result['filename'];
		}
		else
		{
			return __METHOD__ . ': Errors!';
		}
	}
}
