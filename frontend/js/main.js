var Latitude
var Longtitude

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
    Longitude = position.coords.longitude;
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
 
      // "singleItem:true" is a shortcut for:
      // items : 1, 
      // itemsDesktop : false,
      // itemsDesktopSmall : false,
      // itemsTablet: false,
      // itemsMobile : false 
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
    });

    $('.more-jobs a').click(function(){
      $('.table tr.hide-jobs').toggle();
    });

	 $("#searchbtn").click(function () { 
	 	$("table#mytable tr").remove();
		restinput = $("#restinput").val();
		getLocation();
		$.getJSON("http://websys3.stern.nyu.edu/websysS15GB/websysS15GB6/test2/version1/class.MAP.php",{ query:restinput, lat:Latitude,lon:Longtitude },function(data) {
			$.each(data,function(index,value) {
	   			$("#mytable").append('<tr class="odd wow fadeInUp" data-wow-delay="1s">'+
				                     "<td><p>"+value.R_ID+"</p></td><td><p>"+value.Distance+"</p></td>"+
									   '<td class="tbl-apply"><a href="#">View</a></td></tr>'); 
			});
		});
	 });
	 
	 $("table#mytable td a").click(function () {
		 $("table#mytable tr").remove();
	 });
})

     
// Initializing WOW.JS

 new WOW().init();