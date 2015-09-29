jQuery(document).ready(function($) {

  if ( $('#auth_method').find("option:selected").val() == 'apikey' ) {
    $(".apikey").show();
    $(".creds").hide();
    $("#sendgrid_api").val('api');
    $("#sendgrid_api #smtp").hide();
  } else {
    $(".apikey").hide();
    $(".creds").show();
    $("#sendgrid_api #smtp").show();
  }

  var method = $('#sendgrid_api').find("option:selected").val();
  $('#auth_method').change(function() {
    authMethod = $(this).find("option:selected").val();
    if ( authMethod == 'apikey' ) {
    	$(".apikey").show();
    	$(".creds").hide();
        $("#sendgrid_api").val('api');
        $("#sendgrid_api #smtp").hide();
    } else {
    	$(".apikey").hide();
    	$(".creds").show();
        $('#sendgrid_api').val(method);
        $("#sendgrid_api #smtp").show();
        $("#sendgrid_api").find("#"+method).attr("selected", "selected");
    }
  });
});