var Latitude;
var Longtitude;
var rid_array = [];

function getLocation()
  {
  if (navigator.geolocation)
    {
    navigator.geolocation.getCurrentPosition(showPosition);
    }
  else{
      //handle exception
    }
  }
function showPosition(position)
  {
    Latitude = position.coords.latitude;
    Longtitude = position.coords.longitude;
  }

$(window).load(function() { // makes sure the whole site is loaded
      $('#status').fadeOut(); // will first fade out the loading animation
      $('#preloader').delay(350).fadeOut('slow'); // will fade out the white DIV that covers the website.
      $('body').delay(350).css({'overflow':'visible'});
    })

$(document).ready(function() {
 
  $("#bg-slider").owlCarousel({
      navigation : false, // Show next and prev buttons
      slideSpeed : 100,
      autoPlay: 5000,
      paginationSpeed : 100,
      singleItem:true,
      mouseDrag: false,
      transitionStyle : "fade"
 
      // "singleItem:true" is a shortcut for:
      // items : 1, 
      // itemsDesktop : false,
      // itemsDesktopSmall : false,
      // itemsTablet: false,
      // itemsMobile : false 
  });

  $("#testimonial-slider").owlCarousel({
      navigation : false, // Show next and prev buttons
      slideSpeed : 100,
      pagination : true,
      paginationSpeed : 100,
      singleItem:true,
      mouseDrag: false,
      transitionStyle : "goDown"

  });

    $('.more-jobs a').click(function(e){
      e.preventDefault();
      var $this = $(this);
      $this.toggleClass('more-jobs a');
      if($this.hasClass('more-jobs a')){
        $this.text('View less results');     
      } else {
        $this.text('View more results');
      }
//	  $("#mytable").show()
//	  $("#reviewtable").hide()
    });

    $('.more-jobs a').click(function(){
      $('.table tr.hide-jobs').toggle();
    });

	 $("#searchbtn").click(function () { 
	 	$("table#mytable tr").remove();
		restinput = $("#restinput").val();
		getLocation();
		$("#mytable").append('<tr class="odd wow fadeInUp">'+
																  '<td><p>Restaurant Name</p></td>'+
			                                                 '<td><p>Rating</p></td>'+
															      '<td><p>Review count</p></td></tr>');
		$.getJSON("http://websys3.stern.nyu.edu/websysS15GB/websysS15GB6/release/backend/class.MAP.php",{ query:restinput,usr_Lati:Latitude, usr_Lng:Longtitude },function(data) {
			$.each(data,function(index,value) {
	   			$("#mytable").append('<tr class="odd wow fadeInUp">'+
				                     "<td><p>"+value.Name+"</p></td>"+
									   "<td><p>"+value.Star+"</p></td>"+
									   "<td><p>"+value.R_Count+"</p></td>"+
									   '<td class="tbl-apply"><button class="nav-button">View</button></td></tr>'); 
				rid_array[index] = value.R_ID;
			});
		});
	 });

	$("table#mytable").on('click','button.nav-button',function( event ) {
//	$("#mytable td.tbl-apply").click(function () {
	    $("table#reviewtable tr").remove();
//		$("#mytable").hide()
		var rowindex = $(this).closest('tr').index();
//		$(this).closest('tr').after('<tr class="even wow fadeInUp" data-wow-delay="1.1s">'+
//													  '<td><p>dish</p></td>'+
//													  '<td><p>positive</p></td>'+
//													  '<td><p>negative</p></td>'+
//													  '</tr>');
//    	console.debug('rowindex', rowindex);
		//var r=$("table#mytable").length;
		var resid = rid_array[rowindex-1];
		var html = '<tr class="even wow fadeInUp" data-wow-delay="1.1s">'+
													  '<td><p>value.Dish1+</p></td>'+
													  '<td><p>value.Pos1</p></td>'+
													  '<td><p>value.Neg1</p></td>'+
													  '</tr>';
		$("#reviewtable").append('<tr class="even wow fadeInUp">'+
																  '<td><p>Dish Name</p></td>'+
			                                                 '<td><p>Positive Reviews</p></td>'+
															      '<td><p>Negative Reviews</p></td></tr>');
		$.getJSON("http://websys3.stern.nyu.edu/websysS15GB/websysS15GB6/release/backend/class.R_DETAIL.php",{ R_ID:resid },function(data) {
			$.each(data,function(index,value) {
				//htmlrow = '<tr class="even wow fadeInUp" data-wow-delay="1.1s">'+
				$("#reviewtable").append('<tr class="even wow fadeInUp" data-wow-delay="1.1s">'+				
													  '<td><p>'+value.Dish+'</p></td>'+
													  '<td><p>'+value.Pos+'</p></td>'+
													  '<td><p>'+value.Neg+'</p></td>'+
													  '</tr>');
//				html.concat(htmlrow);
			});
//			$(this).closest('tr').after(html);
		
		});
//		$(this).closest('tr').after(html);
	});
	
	$("#restinput").keyup(function(event){
		if(event.keyCode == 13){
			$("#searchbtn").click();
    	}
	});
	
});												  
//	 function detail() {
 //     var tbl = document.getElementById("mytable");
  //    if(tbl) tbl.parentNode.removeChild(tbl);
  //}

//	 $("#mytable td.tbl-apply").click(function () {
//		 $("table#mytable tr").hide();
//	 });


     
// Initializing WOW.JS

 new WOW().init();