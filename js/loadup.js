$(document).ready(function(){

  var vh = $('header').height();
  var vw = $('header').width();

  picheight = vh;

  if(vh*1920<vw*1101){
    picheight=vw*1101/1920;
  }

  $('header').css({
    "height":vh,
    "background-size":"auto "+ picheight + "px"
  });
  
});