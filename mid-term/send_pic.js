function GetXmlHttpObject()
//根据不同浏览器获取XmlHttp对象
{
	var xmlHttp=null;
	try
	{
 // Firefox, Opera 8.0+, Safari
	xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
	// Internet Explorer
	try
	{
	xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch (e)
		{
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
 return xmlHttp;
}
 
function esc(da){
	//转义了
	da=da.replace(/</g,'<').replace(/>/g,'>').replace(/\"/g,'"');
	return encodeURIComponent(da);
}
 
function send_pic()
{
	var xx = document.getElementById("file").files[0];
	
	if (so == null)
	{
		alert('未连接服务器');
		return;
	}
	if (xx == null)
	{
		alert('未选择图片');
		return;
	}
	var fd = new FormData() //FormData 顾名思义，表单也
	
	//表单中添加项，当然也可以添加别的，比如 fd.append('author', 'kkk')
	fd.append('file', xx) 
 
	var xhr = GetXmlHttpObject();//这里不直接用new XMLHttpRequest()避免浏览器不兼容
	if (xhr == null)
	{
		alert ("Browser does not support HTTP Request")
		return
	} 
	
	//注意！！ 如果fd的append的值是文件
	//在trans.php中$_POST数组将看不到该属性
	//而应该在$_FILES中查看！！
	xhr.open("POST" ,"./trans.php" , true);
	
	xhr.send(fd);
	
	xhr.onload = function(e) {
	
		if (this.status == 200) {
			
			so.send("nr=" + esc(this.responseText) + "&type=img")
			   
			//清空input 的值   
			var oldFile = document.getElementById("file");
            var newFile = document.createElement("input");
            newFile.id = oldFile.id;
            newFile.type = "file";
            oldFile.parentNode.replaceChild(newFile, oldFile);
		
		}
	
	};
}