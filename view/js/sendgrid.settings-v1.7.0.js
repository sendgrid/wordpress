jQuery(document).ready(function($) {

  if ( $('#auth_method').find("option:selected").val() == 'apikey' ) {
    $(".apikey").show();
    $(".credentials").hide();
  } else {
    $(".apikey").hide();
    $(".credentials").show();
  }

  if ( $('#send_method').find("option:selected").val() == 'api' ) {
    $(".port").hide();
  } else {
    $(".port").show();
  }

  $('#auth_method').change(function() {
    authMethod = $(this).find("option:selected").val();
    if ( authMethod == 'apikey' ) {
      $(".apikey").show();
      $(".credentials").hide();
    } else {
      $(".apikey").hide();
      $(".credentials").show();
    }
  });

  $('#send_method').change(function() {
    sendMethod = $(this).find("option:selected").val();
    if ( sendMethod == 'api' ) {
      $(".port").hide();
    } else {
      $(".port").show();
    }
  });
});