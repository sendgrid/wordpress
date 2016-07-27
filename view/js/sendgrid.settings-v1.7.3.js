jQuery(document).ready(function($) {

  var invalid_api_key_placeholder = 'Please set a valid API Key';
  var no_list_placeholder = 'Go to Marketing Campaigns to create a list';
  var select_contact_placeholder = 'Select a contact list';
  var select_page_placeholder = 'Select a page';
  var no_pages_placeholder = 'Please create a page to select';

  if ( $('#auth_method').find( 'option:selected' ).val() == 'apikey' ) {
    $('.apikey').show();
    $('.credentials').hide();
    $('.send_method').show();
  } else if ( $('#auth_method').find( 'option:selected' ).val() == 'credentials' ) {
    $('.apikey').hide();
    $('.credentials').show();
    $('.send_method').show();
  }

  if ( $('#send_method').find( 'option:selected' ).val() == 'api' ) {
    $('.port').hide();
  } else if ( $('#send_method').find( 'option:selected' ).val() == 'smtp' ) {
    $('.port').show();
  }

  $('#auth_method').change( function() {
    authMethod = $(this).find( 'option:selected' ).val();
    if ( authMethod == 'apikey' ) {
      $('.apikey').show();
      $('.credentials').hide();
    } else {
      $('.apikey').hide();
      $('.credentials').show();
    }
  } );

  $('#send_method').change( function() {
    sendMethod = $(this).find( 'option:selected' ).val();
    if ( sendMethod == 'api' ) {
      $('.port').hide();
    } else {
      $('.port').show();
    }
  });

  if ( $('#use_transactional').is( ':checked' ) ) {
    $('#mc_apikey').prop( 'disabled', true );
  } else {
    $('#mc_apikey').prop( 'disabled', false );
  }

  $('#use_transactional').change( function() {
    if ( $(this).is( ':checked' ) ) {
      $('#mc_apikey').prop( 'disabled', true );
    } else if ( $("#mc_api_key_defined_in_env").length == 0 ) {
      $('#mc_apikey').prop( 'disabled', false );
    }
  });

  if ( $('select#select_contact_list option').length == 0 ) {
    if ( $("#mc_api_key_is_valid").length == 0 ) {
      $('#select_contact_list').select2( {
        placeholder: invalid_api_key_placeholder
      } );
    } else {
      $('#select_contact_list').select2( {
        placeholder: no_list_placeholder
      } );
    }

    $('#select_contact_list').prop( 'disabled', true );
  } else {
    $('#select_contact_list').select2( {
      placeholder: select_contact_placeholder
    } );

    $('#select_contact_list').prop( 'disabled', false );
  }

  if ( $('select#signup_select_page option').length == 0 ) {
    $('#signup_select_page').select2( {
      placeholder: no_pages_placeholder
    } );

    $('#select_contact_list').prop( 'disabled', true );
  } else {
    $('#signup_select_page').select2( {
      placeholder: select_page_placeholder
    } );
    
    $('#signup_select_page').prop( 'disabled', false );
  }

  if( $('#mc_list_id_defined_in_env').length != 0 ) {
    $('#select_contact_list').prop( 'disabled', true );

    if ( $('select#select_contact_list option').length != 0 ) {
      var selected_value_text = $('#select_contact_list option[selected="selected"]').text();
      $('#select2-select_contact_list-container').prop('title', selected_value_text);
      $('#select2-select_contact_list-container').html(selected_value_text);
    }    
  }

  if( $('#mc_signup_page_defined_in_env').length != 0 ) {
    $('#signup_select_page').prop( 'disabled', true );
    
    if ( $('select#signup_select_page option').length != 0 ) {
      var selected_value_text = $('#signup_select_page option[selected="selected"]').text();
      $('#select2-signup_select_page-container').prop('title', selected_value_text);
      $('#select2-signup_select_page-container').html(selected_value_text);
    }
  }

});