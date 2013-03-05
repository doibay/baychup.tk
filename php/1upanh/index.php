<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PHP c-Image Uploader 3.1 - chiplove.9xpro</title>
<link rel="stylesheet" href="style.css" type="text/css" />
<script  type="text/ecmascript" src="jquery.js?v=3.1"></script>
<script  type="text/ecmascript" src="script.js?v=3.1"></script>
</head>

<body>
<div class="wrapper">
<?php
if(date('H') >= 18 AND date('H') <= 21)
{
	//die('Hệ thống upload ảnh đóng cửa từ 18h đến 21h để tránh quá tải. Bây giờ là '.date('H:i').' giờ</div>');
}
?>
	<div id="header">
    	<h1>Upload ảnh miễn phí</h1>
        <div class="description">chiplove.9xpro</div>
    </div>
    <div class="body">
    	<div class="option">
        	<div class="rows">
            	<span>Server upload ảnh dự bị</span>
                <a href="http://www1.chiplove9x.info/imageshack/" style="color:red">tại đây</a>
            </div>
        	<div class="rows">
            	<span>Upload từ</span>
                <a href="javascript:;" id="uploadfile">Máy tính</a> | <a href="javascript:;" id="transload">URL</a>
            </div>
            <div class="rows">
            	<span>Watermark</span>
            	<input type="radio" name="watermark" value="1" /> Có
                <input type="radio" name="watermark" value="0" /> Không
            </div>
            <div class="rows">
            	<span>Logo</span>
                <input type="radio" name="logo" value="1" /> 
            	<a href="http://imageshack.us/m/848/1849/chiplovebizlogob.png" target="_blank">Logo site (nhỏ)</a> 
                      
				<input type="radio" name="logo" value="3" /> 
            	<a href="http://a.imageshack.us/img838/2595/chiplovebiz120820114622.png" target="_blank">Logo Teen (nhỏ)</a>

            	<input type="radio" name="logo" value="2" /> 
            	<a href="http://imageshack.us/m/202/253/chiplovebizlogo2.png" target="_blank">Logo Teen (to)</a>         
            </div>
            <div class="rows">
            	<span>Resize</span>
                 <select id="resize">
                    <option value="0">No resize</option>
                    <option value="1">100x...</option>
                    <option value="2">150x...</option>
                    <option value="3">320x...</option>
                    <option value="4">640x...</option>
                    <option value="5">800x...</option>
                    <option value="6">1024x...</option>
                </select>
                <span class="note">(Resize theo chiều rộng của bức ảnh. Chú ý ảnh chỉ thu nhỏ chứ ko phóng to)</span>
            </div>
            <div class="rows">
            	<span>Server</span>
                <select id="server">
            		<option value="1">ImageShack</option>
                	<option value="2">Imgur</option>  
                    <!--<option value="3">Picasa</option>-->                     
            	</select>      
            </div>
            <div class="rows method uploadfile">
                 <div class="upload">
                 	<span>Nhấn Browser để chọn file upload</span>
                 	<div id="embed"></div>
                 </div>
            </div>
            
            <div class="rows method transload">
            	<span>Nhập link ảnh vào để transload</span> 
                <div><textarea class="links"></textarea></div>
                <span class="note">(Mỗi link ảnh 1  dòng, có hỗ trợ link ảnh trong thẻ [IMG])</span>
            	<div><input type="button" class="button" value="Transload" /></div>
            </div>
            <div class="rows warning">Trình duyệt của bạn phải bật javascript để sử dụng công cụ này</div>
        </div><!--/.option-->
    </div>
</div><!--/#wrapper-->
<div class="wrapper">
	<div class="body">
    	<div id="result"></div>
        <div id="status"></div>
        <div id="list" style="display:none">
        	<div class="format">
            	<a href="javascript:;" name="direct">Link trực tiếp</a>
                <a href="javascript:;" name="bbcode">Chèn vào Forum</a>
                <a href="javascript:;" name="html">Chèn vào website</a>
                <a href="javascript:;" name="removesub">Remove sub</a>
            </div>
        	<div><textarea class="links" onclick="this.select()"></textarea></div>
        </div>
    </div>
	
</div>

</body>
</html>
