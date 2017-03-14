<?php

$nocsrf = true;

require_once("/usr/local/pkg/monit/monit.inc");
require_once("guiconfig.inc");
require_once("service-utils.inc");

$pgtitle = "Monit: Configuration";
include_once("head.inc");

if ($_POST['action'] == 'update') {
    $monit['monit_config'] = $_POST['data'];
    write_config();
    monit_write_config();
}

include_once('head.inc');
?>

<!-- file status box -->
<div style="display:none; background:#eeeeee;" id="fileStatusBox">
	<div id="fileStatus"></div>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<div class="content">
			<form>
				<div class="btn-group">
					<p>
						<button type="button" class="btn btn-default btn-sm" onclick="saveFile();"	value="<?=gettext('Save')?>">
							<i class="fa fa-save"></i>
							<?=gettext('Save')?>
						</button>
					</p>
				</div>
				<p class="pull-right">
					<button id="btngoto" class="btn btn-default btn-sm"><i class="fa fa-forward"></i><?=gettext("GoTo Line #")?></button> <input type="number" id="gotoline" size="6" style="padding: 3px 0px;"/>
				</p>
			</form>

			<div id="fbBrowser" style="display:none; border:1px dashed gray; width:98%; padding:10px"></div>

			<script type="text/javascript">
			//<![CDATA[
			window.onload=function() {
				document.getElementById("fileContent").wrap='off';
			}
			//]]>
			</script>
			<textarea id="fileContent" name="fileContent" cols="20" rows="30" class="form-control"><?= base64_decode($monit['monit_config']); ?></textarea>
		</div>
	</div>
</div>

<script type="text/javascript">
//<![CDATA[
	events.push(function(){
		function showLine(tarea, lineNum) {
			lineNum--; // array starts at 0
			var lines = tarea.value.split("\n");
			// calculate start/end
			var startPos = 0, endPos = tarea.value.length;
			for (var x = 0; x < lines.length; x++) {
				if (x == lineNum) {
					break;
				}
				startPos += (lines[x].length+1);
			}
			var endPos = lines[lineNum].length+startPos;
			// do selection
			// Chrome / Firefox
			if (typeof(tarea.selectionStart) != "undefined") {
				tarea.focus();
				tarea.selectionStart = startPos;
				tarea.selectionEnd = endPos;
				return true;
			}
			// IE
			if (document.selection && document.selection.createRange) {
				tarea.focus();
				tarea.select();
				var range = document.selection.createRange();
				range.collapse(true);
				range.moveEnd("character", endPos);
				range.moveStart("character", startPos);
				range.select();
				return true;
			}
			return false;
		}
		$("#btngoto").prop('type','button');
		//On clicking the GoTo button, validate the entered value
		// and highlight the required line
		$('#btngoto').click(function() {
			var tarea = document.getElementById("MonitConfig");
			var gtl = $('#gotoline').val();
			var lines = $("#MonitConfig").val().split(/\r|\r\n|\n/).length;
			if (gtl < 1) {
				gtl = 1;
			}
			if (gtl > lines) {
				gtl = lines;
			}
			showLine(tarea, gtl);
		});
		// Goto the specified line on pressing the Enter key within the "Goto line" input element
		$('#gotoline').keyup(function(e) {
			if(e.keyCode == 13) {
				$('#btngoto').click();
			}
		});
	}); // e-o-events.push()
	function loadFile() {
		$("#fileStatus").html("");
		$("#fileStatusBox").show(500);
		$.ajax(
			"<?=$_SERVER['SCRIPT_NAME']?>", {
				type: "post",
				data: "action=load&file=" + $("#fbTarget").val(),
				complete: loadComplete
			}
		);
	}
	function loadComplete(req) {
		$("#fileContent").show(1000);
		var values = req.responseText.split("|");
		values.shift(); values.pop();
		if (values.shift() == "0") {
			var file = values.shift();
			var fileContent = window.atob(values.join("|"));
			$("#fileContent").val(fileContent);
		} else {
			$("#fileStatus").html(values[0]);
			$("#fileContent").val("");
		}
		$("#fileContent").show(1000);
	}
	function saveFile(file) {
		$("#fileStatus").html("");
		$("#fileStatusBox").show(500);
		var fileContent = Base64.encode($("#fileContent").val());
		fileContent = fileContent.replace(/\+/g, "%2B");
		$.ajax(
			"<?=$_SERVER['SCRIPT_NAME']?>", {
				type: "post",
				data: "action=update&data=" + fileContent,
				complete: function(req) {
					var values = req.responseText.split("|");
					$("#fileStatus").html(values[1]);
				}
			}
		);
	}
/**
 *
 *	Base64 encode / decode
 *	http://www.webtoolkit.info/
 *	http://www.webtoolkit.info/licence
 **/
var Base64 = {
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
		input = Base64._utf8_encode(input);
		while (i < input.length) {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
		}
		return output;
	},
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (i < input.length) {
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			output = output + String.fromCharCode(chr1);
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
		}
		output = Base64._utf8_decode(output);
		return output;
	},
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
		for (var n = 0; n < string.length; n++) {
			var c = string.charCodeAt(n);
			if (c < 128) {
				utftext += String.fromCharCode(c);
			} else if ((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			} else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while (i < utftext.length) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			} else if ((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			} else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
};
	<?php if ($_POST['action'] == "load"): ?>
		events.push(function() {
			$("#fbTarget").val("<?=htmlspecialchars($_POST['path'])?>");
			loadFile();
		});
	<?php endif; ?>
//]]>
</script>

<?php include("foot.inc");
