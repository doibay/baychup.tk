var iload = true;
var iloadPosition = 0;
var loadingSetTimeOut = null;
//watermark
var watermark = getCookie('watmermark') ? true : false;
var logo = getCookie('logo') ? getCookie('logo') : 1;
//resize
var resize = getCookie('resize') ? getCookie('resize') : 4;


function loading() {
    document.getElementById('loading').innerHTML = 'Uploading..<span id="iload"></span>';
	iload = true;
    iloading();
}
function stopLoading() {
	iload = false;
	iloadPosition = 0;
	 if (document.getElementById('iload') != null) {
		document.getElementById('iload').innerHTML = '';
	}
	document.getElementById('loading').innerHTML = '';
	if(loadingSetTimeOut != null) {
		clearTimeout(loadingSetTimeOut);
	}
}

function iloading() {
    if (iload) {
        iloadPosition++;
       // var icon = new Array('|', '/', '-', '\\');
         var icon = new Array('.', '..', '...', '....','.....');
        if (iloadPosition > icon.length -1) {
            iloadPosition = 0;
        }
        if (document.getElementById('iload') != null) {
            document.getElementById('iload').innerHTML = icon[iloadPosition];
        }
        loadingSetTimeOut = setTimeout('iloading();', 160);
    } else {
        stopLoading();
    }
}

function setLogo(_logo) {
	logo = _logo;
    setCookie('logo', logo, 365);
	if(document.getElementById('flash_upload') != null) {
		renderUpload(getWatermark());
	}
}
function getLogo(){
	return getCookie('logo') ? getCookie('logo') : 1;
}


function chooseResize() {
    setCookie('resize', document.getElementById('resize').value, 365);
	if(document.getElementById('flash_upload') != null) {
		renderUpload(watermark);
	}
}

function getResize() {
    return getCookie('resize') ? getCookie('resize') : 4; 
}
function setWatermark(watermark) {
    setCookie('watermark', watermark ? 1 : 0, 365);
	if(document.getElementById('flash_upload') != null) {
		renderUpload(watermark ? 1 : 0);
	}
}
function getWatermark() {
	return getCookie('watermark') == 1 ? 1 : 0;
}
function renderUpload(wtm) {
    document.getElementById('flash_upload').innerHTML = '<embed type="application/x-shockwave-flash" quality="high" wmode="transparent" ' + 'pluginurl="http://www.macromedia.com/go/getflashplayer" pluginspage="http://www.macromedia.com/go/getflashplayer" src="upload.swf?' + Math.random() + '&amp;' + (getWatermark() ? 'watermark=1' : 'watermark=0') + '" width="75" height="25"></embed>';

}

function responseStatus(msg) {
    if (msg == 'Done!') {
        stopLoading();
        transfer_id = 0;   
		//auto remove sub when upload done
		if(getCookie('remove_sub') == 1) {
			remove_sub = 0;
			removesub();
		}else {
        	setTimeout("showcode('bbcode');", 0000);
		}
		document.getElementById('getcode').style.display = 'block';
    }
	document.getElementById('loading').innerHTML = msg;
}

function clearlist() {
    document.getElementById('getcode').style.display = 'none';
    document.getElementById('result').innerHTML = "";
    stopLoading();
}

var remove_sub = getCookie('remove_sub') ? 1 : 0;

function removesub(_auto) {
    var str = document.getElementById('result').innerHTML;
    re = /(https?:\/\/)([^\.]*?)(\.imageshack\.us\/)(img[^\.\/]*)(.*?\.)(jpg|png|bmp|gif|jpeg)/ig;
    m = re.exec(str);
    var str2 = '';
	//remove sub img to a
    if (remove_sub == 0) {
        while (m) {
            str = str.replace(m[1] + m[2], m[1] + 'a');
            m = re.exec(str);
        }
        remove_sub = 1;
    } else {
        while (m) {
            str = str.replace(m[1] + m[2], m[1] + m[4]);
            m = re.exec(str);
        }
        remove_sub = 0;
    }
	setCookie('remove_sub', remove_sub, 365);
    document.getElementById('result').innerHTML = str;
    showcode(show_type);
}

var show_type = getCookie('show_type') ? getCookie('show_type') : 'none';

function showcode(type) {
    show_type = type;
	setCookie('show_type', show_type, 365);
    var code = new Array();
    if (type == 'html') {
        code[0] = '&lt;img src="';
        code[1] = '" /&gt;';
    } else if (type == 'bbcode') {
        code[0] = '[IMG]';
        code[1] = '[/IMG]';
    } else {
        code[0] = '';
        code[1] = '';
    }
    var content = document.getElementById('result').innerHTML;
    var html = '';
    var re = /value="(https?:.*?)\.(jpg|png|bmp|gif|jpeg)"/ig;
    var m = re.exec(content);
    while (m) {
		if(!code[0] && type!='none'){
			myArr = m[1].split('/');
			last = myArr[myArr.length-1];
			last = 's'+type+'/'+last;
			myArr[myArr.length-1] = last;
			m[1] = myArr.join('/');
		}
        html += code[0] + m[1] + '.' + m[2] + code[1] + "\r\n";
        m = re.exec(content);
    }
    document.getElementById('showcode').innerHTML = html;
}

var transfer_id = 0;

function transfer(id) {
    transfer_id = (!id) ? transfer_id : id;
    var _wtm = document.getElementById('watermark').checked ? '1' : '0';
    text = document.getElementById('listurl').value;
    var re = /.*?(\[IMG\])?(https?[^\[\'\"\;]*\.(jpg|png|bmp|gif|jpeg))(\[\/IMG\])?/ig;
    var m = re.exec(text);
    var html = "";
    while (m) {
       html += m[2] +"\n";
        m = re.exec(text);
    }
    ex = html.split("\n");
	if(!ex[0]) return false;
	var totalimg = ex.length;
    url = ex[transfer_id].replace("\n", "");
	transferUrl(url, transfer_id, totalimg, _wtm);

}

function transferUrl(url, transfer_id, total, _wtm) {
	loading();
    var ajax = new AJAX_Handler();
    ajax.onreadystatechange(check);
    transfer_id += 1;
    ajax.send('upload.php', 'url=' + encodeURI(url) + '&watermark=' + _wtm + '&sid=' + Math.random());

    function check() {
        if (ajax.xmlHttp.readyState == 4 && ajax.xmlHttp.status == 200) {
            stopLoading();
            dv = document.createElement("div");
            dv.innerHTML = transfer_id + '/' + (total-1) + ' <input value="' + ajax.xmlHttp.responseText.split('image=')[1] + '" onclick="this.select()" size="30" class="input" readonly="readonly" />';
            if (transfer_id == total - 1) {
				responseStatus("Done!");
			}
            else {
				transfer(transfer_id);
			}
            document.getElementById("result").appendChild(dv);
        }
    }
}

function displaypic(name, url) {
    dv = document.createElement("div");
    dv.innerHTML = '<b>' + name + ':</b><br /><input value="' + url + '" onclick="this.select()" size="30" class="input" readonly="readonly" />';
    document.getElementById('result').appendChild(dv);
	stopLoading();
}

function setCookie(c_name, value, exdays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
    document.cookie = c_name + "=" + c_value;
}

function getCookie(c_name) {
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i = 0; i < ARRcookies.length; i++) {
        x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
        y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
        x = x.replace(/^\s+|\s+$/g, "");
        if (x == c_name) {
            return unescape(y);
        }
    }
    return null;
}

function AJAX_Handler() {
    this.xmlHttp = false;
    try {
        this.xmlHttp = new XMLHttpRequest();
    } catch (e) {
        try {
            this.xmlHttp = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (e) {
            try {
                this.xmlHttp = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                alert('Your browser does not support AJAX');
                return;
            }
        }
    }
    this.onreadystatechange = function (updateFunc) {
        this.xmlHttp.onreadystatechange = updateFunc;
    }
    this.send = function (url, param) {
        param = param ? param : "";
        this.xmlHttp.open("POST", url, true);
        this.xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        this.xmlHttp.setRequestHeader("Content-length", param.length);
        this.xmlHttp.setRequestHeader("Connection", "close");
        this.xmlHttp.send(encodeURI(param));
    }
}