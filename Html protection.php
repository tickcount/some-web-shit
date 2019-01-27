<table border="0" align="center"><tbody>
	<tr><td><form name="conv_form"><textarea name="src_text" cols="50" rows="10" wrap="VIRTUAL"></textarea></form></td></tr>
	<tr><td><input value="Code!" onclick="Code(); return 0;" type="button"></td></tr>
</tbody></table>

<script>
	function Code(){
		var temp = "", i, l, c = 0, out = "";
		var str = document.conv_form.src_text.value;
		rnd = Math.floor(Math.random() * (99999999999 - 1)) + 1;
		l = 0; if(str == "") return;
		while(l<=str.length-1) {
			out = out + str.charCodeAt(l) + '!';
			l++;
		}
		document.conv_form.src_text.value = "<script id=\"" + rnd + "\" author=\"slv\"> function Decode(){var temp=\"\",i,c=0,out=\"\";var str=\"" + out + "\";l=str.length;document.getElementById(\"" + rnd + "\").remove();while(c<=str.length-1){while(str.charAt(c)!=\'!\')temp=temp+str.charAt(c++);c++;out=out+String.fromCharCode(temp);temp=\"\";} document.write(out); } Decode(); <\/script>";  
	}
</script>