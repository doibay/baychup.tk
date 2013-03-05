<?php

error_reporting(E_ALL & ~E_NOTICE);
session_start();

define('DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
include (DIR . 'inc/class_image.php');
include (DIR . 'inc/class_image_uploader.php');

// fiter
$params = array('server', 'resize', 'watermark', 'logo');
foreach($params as $param)
{
	$name = $param . 'id';
	$data = intval($_REQUEST[$param]);
	if($data < 0) 
	{
		$data = 0;
	}
	${$name} = $data;
}


##################### START CONFIG #######################

$sitename = 'chiplovebiz';
/**
 * Tạo và CHMOD folder này sang 777
*/
$tempdir = DIR . 'temp/';

// danh sách logo
$logolist = array(
	1 => 'logo1.png', 
	2 => 'logo2.png',
	3 => 'logo3.png',
);
// Nếu logo yêu cầu ko có trong danh sách thì dùng logo1.png 
$default['logo'] = 'logo1.png';

// vị trí logo (right bottom, right center, right top, left top, .v.v.)
$logoPosition = 'rb';



// kích cỡ resize
$resizelist = array(
	0	=> 0, // ko resize
	1	=> 100, 
	2	=> 150,
	3	=> 320,
	4	=> 640,
	5	=> 800,
	6	=> 1024
);
$default['resize'] = 800;


##################### END CONFIG #######################



$watermark = $watermarkid > 0 ? TRUE : FALSE;

$logoPath = DIR . 'logo/' . (in_array($logoid, array_keys($logolist)) ? $logolist[$logoid] : $default['logo']);

$resizeWidth = in_array($resizeid, array_keys($resizelist)) ? $resizelist[$resizeid] : $default['resize'];


if($_FILES['Filedata'] AND !$_FILES['Filedata']['error'])
{
	move_uploaded_file($_FILES['Filedata']['tmp_name'], $imagePath = $tempdir . $sitename .date('dmY'). '.jpg');
	$isUpload = TRUE;
}
else if($url = trim($_POST['url']))
{
	$isUpload = FALSE;
	c_Image::leech($url, $imagePath = $tempfolder . $sitename . date('dmY').'.jpg');
}


// resize
if($resizeWidth > 0)
{
	c_Image::resize($imagePath, $resizeWidth, 0);
}
// watermark
if($watermark)
{
	c_Image::watermark($imagePath, $logoPath, $logoPosition);
}

switch($serverid)
{
	case 1:	
		$service = 'imageshack';
		break;
	case 2:
		$service = 'imgur';
		break;
	case 3:
		$service = 'picasa';
		break;
	default:
		$service = 'imageshack';			
}

$uploader = c_Image_Uploader::factory($service);

switch($service)
{
	case 'imageshack':
		/**
		 * Không bắt buộc đăng nhập
		 * Có thể đăng nhập hoặc ko. Tuy nhiên nên tham khảo quy định của ImageShack ở đây http://imageshack.us/content.php?page=rules
		 * Nếu có API key thì điền vào (để upload nhanh hơn)
		 * Mỗi account chỉ được up 500 ảnh free. Nếu ko muốn up vào account của mình thì thêm dấu # vào đầu dòng của login
		 * VD:
		 * #$uploader->login('your user', 'your pass');
		 *
		 * Nếu ko điền cả user và API key thì code sẽ tự động get key free của imageshack. 
		*/
		//$uploader->login('your user', 'your pass');
		//$uploader->set_api('your api key');
		break;
		
	case 'imgur':
		/**
		 * Plugin này mình chưa làm phần login, mới chỉ làm phần upload free lên thôi.
		 * Phần này các bạn ko phải cấu hình gì cả
		*/
		break;	
	case 'picasa':
		/**
		 * Picasa bắt buộc phải đăng nhập 
		 * AlbumID lấy ở link RSS trong album (ko biết thì tự tìm hiểu ở google)
		 * Phần albumID có thể set 1 array('id1', 'id2'); Code sẽ tự động lấy ngẫu nhiên 1 album trong số đó để upload vào.
		 * Nếu ko setAlbumID thì code sẽ up vào album default của picasa 
		 * Mỗi album chứa được 1000 ảnh. Mỗi account chứa đc 10000 ảnh.
		 * Nếu ko dùng AlbumID thì thêm dấu # ở trước
		*/
		$uploader->login('baychup@gmail.com', '123doibay');
		$uploader->setAlbumID('5839468809189278353');
		
		return;
		break;	
}

if(!$imagePath)
{
	die('Mising an image');
}
$url = $uploader->upload($imagePath);

if(file_exists($imagePath)) 
{
	unlink($imagePath);
}

if($isUpload)
{
	echo 'image=' . $url;
}
else
{
	echo $url;
}
