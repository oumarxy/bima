/**
 * function showComponent()
 * ajax function to display drop menu on selected value
 * author Lackson David <lacksinho@gmail.com>
 * written on Saturday,April 13 2013
 * */

function Ajax(){
	if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
   return xmlhttp;
	}


function showPropertyNumber(id,str)
{
    var html="";
    var str=str;
if (id==1)
  {
   html="<div class=\"form-group\"><label  class=\"col-sm-4 control-label\">Number: <span class=\"text-red\">*</span></label>";  
   html=html+"<div class=\"col-sm-8\"><input type='text' name='number' value='"+ str +"'/></div></div>";
  }
  document.getElementById("txtnumber").innerHTML=html;
  return;
}
