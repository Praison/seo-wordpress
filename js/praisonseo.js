function textLength(value){
   return value.length;
}

function metaDescCount(value){
	var meta_desc_count = document.getElementById('zeo_description_count');
	if(textLength(value)<= 320){
     		meta_desc_count.innerHTML = textLength(value);
        meta_desc_count.style.color = "darkgreen";
     }else{
     		meta_desc_count.innerHTML = textLength(value);
        meta_desc_count.style.color = "red";
     }
}

function metaTitleCount(value){
	var meta_title_count = document.getElementById('zeo_title_count');
	 if(textLength(value)<= 70){
	 		meta_title_count.innerHTML = textLength(value);
	    meta_title_count.style.color = "darkgreen";
	 }else{
	 		meta_title_count.innerHTML = textLength(value);
	    meta_title_count.style.color = "red";
	 }
}

document.getElementById('zeo_description').onkeyup = function(){     				
    metaDescCount(this.value);
}

document.getElementById('zeo_title').onkeyup = function(){	
	metaTitleCount(this.value);
}

				
metaDescCount(document.getElementById('zeo_description').value);

metaTitleCount(document.getElementById('zeo_title').value);
