jQuery(document).ready(function($) {

  if ( $('#auth_method').find("option:selected").val() == 'apikey' ) {
    $(".apikey").show();
    $(".creds").hide();
    $("#sendgrid_api").val('api');
    $("#sendgrid_api #smtp").hide();
    $(".port").hide();
  } else {
    $(".apikey").hide();
    $(".creds").show();
    $("#sendgrid_api #smtp").show();
    $(".port").show();
  }

  var method = $('#sendgrid_api').find("option:selected").val();
  $('#auth_method').change(function() {
    authMethod = $(this).find("option:selected").val();
    if ( authMethod == 'apikey' ) {
      $(".apikey").show();
      $(".creds").hide();
      $(".port").hide();
      $("#sendgrid_api").val('api');
      $("#sendgrid_api #smtp").hide();
    } else {
      $(".apikey").hide();
      $(".creds").show();
      if (method == 'smtp') {
        $(".port").show();
      }
      $('#sendgrid_api').val(method);
      $("#sendgrid_api #smtp").show();
      $("#sendgrid_api").find("#"+method).attr("selected", "selected");
    }
  });

  $('#sendgrid_api').change(function() {
    sendMethod = $(this).find("option:selected").val();
    if ( sendMethod == 'api' ) {
      $(".port").hide();
    } else {
      $(".port").show();
    }
  });

  if ( method == 'api' ) {
    $(".port").hide();
  } else {
    $(".port").show();
  }
});