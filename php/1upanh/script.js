/**
 * c-Image Uploader, use to upload image to some services (picasa, imageshack, imgur ...etct
 *
 * @project		Image Uploader
 * @author		Phan Thanh Cong <chiplove.9xpro@gmail.com>
 * @since		June 17, 2010
 * @version		3.1
 * @since		March 8, 2012
 * @copyright	chiplove.9xpro
*/

// Default options
var options = {
	watermark:	1, // 1 or 0
	logo:		2, // danh sách logo trong file upload
	resize:		5, // tương tự logo
	server:		1, // trong file upload và index
	format:		'bbcode',	//danh sách các link trả về đc format theo bbcode, html và link trực tiếp
	removesub:	0, // dành cho link của imageshack.us
	method:		'uploadfile', // uploadfile/ transload
}

$(function(){
	renderUploader();
	clearlist();	
	$(".warning").hide();

	// set default
	$('input:radio[name=watermark]').removeAttr("checked").filter('[value='+getWatermark()+']').attr('checked', true);

	$('input:radio[name=logo]').removeAttr("checked").filter('[value='+getLogo()+']').attr('checked', true);

	$("#resize option").filter('[value='+getResize()+']').attr('selected', true);
	
	$("#server option").filter('[value='+getServer()+']').attr('selected', true);
	
	$(".method").hide();
	$("."+getMethod()).slideDown();
	
	// change value 
	$('input:radio').click(function(){
		$.cookie($(this).attr('name'), $(this).val());
		renderUploader();	
	});
	$("#resize, #server").change(function(){
		$.cookie($(this).attr('id'), $(this).val());
		renderUploader();	
	});
	
	$("#uploadfile").click(function(){
		$(".transload").hide();
		$(".uploadfile").slideDown();
		$.cookie('method', 'uploadfile');
		renderUploader();	
		clearlist();
	});
	$("#transload").click(function(){
		$(".uploadfile").hide();
		$(".transload").slideDown();
		$.cookie('method', 'transload');
		clearlist();
	});
	
	$(".format a").click(function() {
		var name = $(this).attr('name');
		if(name != 'removesub') {
			$.cookie('format', name);
		}
		else {
			var removesub = getRemovesub() == 0 ? 1 : 0;
			$.cookie('removesub', removesub);
		}
		showList();
	});
	
	// transload click
	$(".transload .button").click(function(){
		transload();
	});
	
});

function getRemovesub() {
	return $.cookie('removesub') == null ? options.removesub : $.cookie('removesub');
}
function getFormat() {
	return $.cookie('format') == null ? options.format : $.cookie('format');
}
function getMethod() {
	return $.cookie('method') == null ? options.method : $.cookie('method');
}
function getWatermark() {
	return $.cookie('watermark') == null ? options.watermark : $.cookie('watermark');
}
function getLogo() {
	return $.cookie('logo') == null ? options.logo : $.cookie('logo');
}
function getResize() {
	return $.cookie('resize') == null ? options.resize : $.cookie('resize');
}
function getServer() {
	return $.cookie('server') == null ? options.server : $.cookie('server');
}


function renderUploader() {
	var html = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="75" height="25"> \
		<param name="movie" value="upload.swf" /> \
		<param name="wmode" value="transparent" /> \
		<param name="allowFullScreen" value="true" /> \
		<param name="allowScriptAccess" value="always" />  \
		<param name="watermark" value="'+getWatermark()+'" /> \
		<param name="logo" value="'+getLogo()+'" /> \
		<param name="resize" value="'+getResize()+'" /> \
		<param name="server" value="'+getServer()+'" /> \
		<embed name="flashplayer" src="upload.swf" flashvars="'
			+'watermark='+getWatermark()
			+'&logo='+getLogo()
			+'&resize='+getResize()
			+'&server='+getServer()
		+'" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="75" height="25" wmode="transparent"></embed> \
		</object>';
	$("#embed").html(html);	
}

var iloadPosition = 0;
var iload = false;
var loadInterval = 0;
function loading(status) {
	iloadPosition = 0;
	if(status == false) {
		iload = false;
		if(document.getElementById('process') != null) {
			$('#process').html('');	
			$('#status').html('');
		}
		clearInterval(loadInterval);
		loadInterval = 0;
	}
	else {
		iload = true;
		$('#status').html('Loading <span id="process"></span>');
		if( loadInterval <= 0) {
			loadInterval = setInterval('iloading();',160);
		}
	}
}
function iloading() {
    if (iload) {
       // var icon = new Array('|', '/', '-', '\\');
        var icon = new Array('.', '..', '...', '....');
		//var icon = new Array('|', '||', '|||', '||||', '|||||', '||||||');
		iloadPosition = iloadPosition >= icon.length ? 0 : iloadPosition;
        $('#process').html(icon[iloadPosition]);
		iloadPosition++;
    } 
}

function responseStatus(msg) {
    if (msg == 'Done!') {
        loading(false);
		showList();
    }
	$('#status').html(msg);
}

// hàm hiển thị ảnh
function displaypic(name, url) {
	$("#result").append('<div><span class="name">'+name+'</span><input type="text" class="link" onclick="this.select()" value="'+url+'" /></div>');
	showList();
}
// xóa list link trả về
function clearlist() {	
	$('#status').html('');
	$("#result").html('');
	$("#list .links").val('');
}
function showList() {
	$("#list").slideDown();
	var code;
    if (getFormat() == 'html') {
		code = new Array('<img src="', '" />');
    } 
	else if (getFormat() == 'bbcode') {
        code = new Array('[IMG]', '[/IMG]');
    } 
	else {
        code = new Array('', '');
    }
	var links = "";
	$("#result .link").each(function() {
		var url = $(this).val();
		if(getRemovesub()) {
			url = removesub(url);
			$(this).val(url);
		}
		if(url.substring(0, 4) != 'http') {
			return;
		}
		links += code[0] + url + code[1] +"\n";
	});
	$("#list .links").slideDown().val(links);
}

function removesub(url) {
    re = /(https?:\/\/)([^\.]*?)(\.imageshack\.us\/)(img[^\.\/]*)(.*?\.)(jpg|png|bmp|gif|jpeg)/ig;
    if(m = re.exec(url)) {
		if (getRemovesub() == 0) {
			url = url.replace(m[1] + m[2], m[1] + 'a');
		} else {
			url = url.replace(m[1] + m[2], m[1] + m[4]); 
		}
	}
	return url;
}
/****** TRANSLOAD ****/
function getInputLinks() {
	var text = $(".transload .links").val();
	var re = /.*?(\[IMG\])?(https?[^\\\n[]+)(\[\/IMG\])?/ig;
    var m;
	var links = new Array();
	var i = 0;
    while (m = re.exec(text)) {
       links[i++] = $.trim(m[2]);
    }
	return links;
}
function doTransload(id, links) {
	var stt = id + 1;
	var url = links[id];
	$.ajax({
		data: {
			'url':		 	url, 
			'watermark': 	getWatermark(),
			'resize':		getResize(),
			'server': 		getServer(),
			'logo':			getLogo(),
		},
		url: 'upload.php',	
		cache: false,
		type: "POST",
		success: function(msg){
			displaypic(stt, msg);
			if(stt < links.length) {
				doTransload(stt, links);
			}
			else {
				responseStatus('Done!');
			}
		},
		error: function(){
			alert("Có lỗi, vui lòng thử lại");
		},
	});
}
function transload() {
	clearlist();
	var links = getInputLinks();
	if(links.length == 0) {
		alert('Bạn cần phải nhập đường dẫn ảnh');
		return;
	}
	loading();
	doTransload(0, links);
}