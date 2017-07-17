<?php

require_once plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'sendgrid-api-v3.php';

class Sendgrid_Translator {
  /**
   * Checks if the specified variable holds a non-empty string
   *
   * @param   type  string  $input_string
   *
   * @return  bool
   */
  private static function is_valid_string( $input_string ) {
    if ( is_string( $input_string ) and
      strlen( trim( $input_string ) ) > 0 ) {
      return true;
    }

    return false;
  }

  /**
   * Returns an array of filter settings for the specified filter key from the SMTPAPI header of a v2 Email
   *
   * @param   type  SendGrid\Email    $email_v2
   * @param   type  string            $filter_key
   * @param   type  array             $filter_settings
   * @param   type  string            $filter_enabled
   *
   * @return  array
   */
  private static function get_smtp_filter_settings(
    SendGrid\Email  $email_v2,
                    $filter_key, 
                    $filter_settings,
                    $filter_enabled   = 'enable'
  ) {
    $filter_sub_label = 'settings';
    $output_array     = array();

    if ( ! is_array( $filter_settings ) ) {
      return $output_array;
    }

    // Check that the SMTPAPI header filter object is not malformed
    if ( ! is_array( $email_v2->smtpapi->filters ) ) {
      return $output_array;
    }

    // Check that the filter object exists
    if ( ! isset( $email_v2->smtpapi->filters[ $filter_key ] ) ) {
      return $output_array;
    }

    // Check that 'settings' exist under filter
    if ( ! isset( $email_v2->smtpapi->filters[ $filter_key ][ $filter_sub_label ] ) ) {
      return $output_array;
    }

    // Avoid PHP warning when foreaching for settings by making sure it's an array
    if ( ! is_array( $email_v2->smtpapi->filters[ $filter_key ][ $filter_sub_label ] ) ) {
      return $output_array;
    }

    // Make sure there is an enabled flag
    if ( ! isset( $email_v2->smtpapi->filters[ $filter_key ][ $filter_sub_label ][ $filter_enabled ] ) ) {
      return $output_array;
    }

    // If it's not enabled, return empty array, no need to make the payload bigger
    if ( ! $email_v2->smtpapi->filters[ $filter_key ][ $filter_sub_label ][ $filter_enabled ]  ) {
      return $output_array;
    }

    foreach ( $email_v2->smtpapi->filters[ $filter_key ][ $filter_sub_label ] as $setting_key => $setting_value ) {
      if ( in_array( $setting_key, $filter_settings ) ) {
        $output_array[ $setting_key ] = $setting_value;
      }
    }
    
    return $output_array;
  }

  /**
   * Sets the From address and FromName (if set) to a V3 Email from a V2 Email
   *  - for API V3 the From email address is mandatory and it may not include Unicode encoding
   *  - for API V3 the FromName is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_from_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $from_name = null;

    if ( isset( $email_v2->fromName ) and self::is_valid_string( $email_v2->fromName ) ) {
      $from_name = trim( $email_v2->fromName );
    }

    $from = new SendGridV3\Email( $from_name, trim( $email_v2->from ) );
    $email_v3->setFrom( $from );
  }

  /**
   * Sets the Subject (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Subject field is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_subject_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( isset( $email_v2->subject ) and self::is_valid_string( $email_v2->subject ) ) {
      $email_v3->setSubject( $email_v2->subject );
    }
  }

  /**
   * Sets the plaintext content (if set) to a V3 Email from a V2 Email
   *  - for API V3 at least one content object must be present (either plaintext or html)
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_text_content_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( isset( $email_v2->text ) and self::is_valid_string( $email_v2->text ) ) {
      $text_content = new SendGridV3\Content( 'text/plain', $email_v2->text );
      $email_v3->addContent($text_content);
    }
  }

  /**
   * Sets the HTML content (if set) to a V3 Email from a V2 Email
   *  - for API V3 at least one content object must be present (either plaintext or html)
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_html_content_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( isset( $email_v2->html ) and self::is_valid_string( $email_v2->html ) ) {
      $html_content = new SendGridV3\Content( 'text/html', $email_v2->html );
      $email_v3->addContent($html_content);
    }
  }

  /**
   * Sets the To addresses and ToNames (if set) to a V3 Personalization Object from a V2 Email
   *  - for API V3 at least one recipient (To email address) must be present
   *  - for API V3 the To Name is optional
   *  - also adds substitutions, custom args and send each at, if present for each email
   *
   * @param   type  SendGridV3\Mail               $email_v3
   * @param   type  SendGrid\Email                $email_v2
   *
   * @return  void
   */
  private static function set_tos_v3(
    SendGridV3\Mail             $email_v3,
    SendGrid\Email              $email_v2
  ) {
    if ( ! is_array( $email_v2->to ) ) {
      return;
    }

    // Create a new personalization for this To
    $personalization  = new SendGridV3\Personalization();

    foreach ( $email_v2->to as $index => $address ) {
      if ( ! self::is_valid_string( $address ) ) {
        continue;
      }

      $to_name      = null;
      $to_address   = trim( $address );

      if ( isset( $email_v2->toName[ $index ] ) and
        self::is_valid_string( $email_v2->toName[ $index ] ) ) {
        $to_name = trim( $email_v2->toName[ $index ] );
      }

      $recipient = new SendGridV3\Email( $to_name, $to_address );

      // Add the values
      $personalization->addTo( $recipient );
      self::set_substitutions_v3( $index, $personalization, $email_v2 );
      self::set_custom_args_v3( $index, $personalization, $email_v2 );
      self::set_send_each_at_v3( $index, $personalization, $email_v2 );
    }

    // Append the personalization to the email
    $email_v3->addPersonalization( $personalization );
  }

  /**
   * Sets the CC addresses and CCNames (if set) to a V3 Personalization Object from a V2 Email
   *  - for API V3 the CC addresses are optional
   *  - for API V3 the CC Name is optional for all CC addresses
   *
   * @param   type  SendGridV3\Personalization    $personalization
   * @param   type  SendGrid\Email                $email_v2
   *
   * @return  void
   */
  private static function set_ccs_v3(
    SendGridV3\Personalization  $personalization,
    SendGrid\Email              $email_v2
  ) {
    if ( ! is_array( $email_v2->cc ) ) {
      return;
    }

    foreach ( $email_v2->cc as $index => $address ) {
      // Check if "cc name" is set
      $cc_name = null;
      if ( self::is_valid_string( $email_v2->ccName[ $index ] ) ) {
        $cc_name = trim( $email_v2->ccName[ $index ] );
      }

      $recipient = new SendGridV3\Email( $cc_name, $address );
      $personalization->addCc( $recipient );
    }
  }

  /**
   * Sets the BCC addresses and BCCNames (if set) to a V3 Personalization Object from a V2 Email
   *  - for API V3 the BCC addresses are optional
   *  - for API V3 the BCC Name is optional for all BCC addresses
   *
   * @param   type  SendGridV3\Personalization    $personalization
   * @param   type  SendGrid\Email                $email_v2
   *
   * @return  void
   */
  private static function set_bccs_v3(
    SendGridV3\Personalization  $personalization,
    SendGrid\Email              $email_v2
  ) {
    if ( ! is_array( $email_v2->bcc ) ) {
      return;
    }

    foreach ( $email_v2->bcc as $index => $address ) {
      // Check if "bcc name" is set
      $bcc_name = null;
      if ( self::is_valid_string( $email_v2->bccName[ $index ] ) ) {
        $bcc_name = trim( $email_v2->bccName[ $index ] );
      }

      $recipient = new SendGridV3\Email( $bcc_name, $address );
      $personalization->addBcc( $recipient );
    }
  }

  /**
   * Sets the ReplyTo address (if set) to a V3 Email from a V2 Email
   *  - for API V3 the ReplyTo email address is optional and it may not include Unicode encoding
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_reply_to_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( isset( $email_v2->replyTo ) and self::is_valid_string( $email_v2->replyTo ) ) {
       $email_v3->setReplyTo( new SendGridV3\Email( null, trim( $email_v2->replyTo ) ) );
    }
  }

  /**
   * Sets the Headers (if set) to a V3 Email from a V2 Email
   *  - for API V3 the CC addresses are optional
   *  - for API V3 the CC Name is optional for all CC addresses
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_headers_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! is_array( $email_v2->headers ) ) {
      return;
    }

    foreach ( $email_v2->headers as $header => $value ) {
      $email_v3->addHeader( $header, $value );
    }
  }

  /**
   * Sets the Attachments (if set) to a V3 Email from a V2 Email
   *  - only attaches file if it's present at specified path and readable
   *  - only the content and filename fields are mandatory
   *  - content field must be base64 encoded
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_attachments_v3( 
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! is_array( $email_v2->attachments ) ) {
      return;
    }

    foreach ( $email_v2->attachments as $index => $file_info ) {
      if ( ! isset( $file_info[ 'file' ] ) or ! isset( $file_info[ 'basename' ] ) ) {
        continue;
      }

      $file_contents = file_get_contents( $file_info[ 'file' ] );
  
      // file_get_contents retuns a bool or non-bool which evaluates to false if it fails
      if ( ! $file_contents ) {
        continue;
      }

      $file_contents = base64_encode( $file_contents );

      // base64_encode returns a bool or non-bool which evaluates to false if it fails
      if ( ! $file_contents ) {
        continue;
      }

      $attachment = new SendGridV3\Attachment();
      $attachment->setContent( $file_contents );
      $attachment->setFilename( $file_info[ 'basename' ] );

      // Set the custom filename if specified
      if ( isset( $file_info[ 'custom_filename' ] ) and
        self::is_valid_string( $file_info[ 'custom_filename' ] ) ) {
          $attachment->setFilename( trim( $file_info[ 'custom_filename' ] ) );
      }

      // Set the Content ID if specified
      if ( isset( $file_info[ 'cid' ] ) and
        self::is_valid_string( $file_info[ 'cid' ] ) ) {
        $attachment->setContentID( trim( $file_info[ 'cid' ] ) );
      }

      $email_v3->addAttachment( $attachment );
    }
  }

  /**
   * Sets the Substitution (if set) to a V3 Personalization from a V2 Email
   *
   * @param   type  integer                       $index
   * @param   type  SendGridV3\Personalization    $personalization
   * @param   type  SendGrid\Email                $email_v2
   *
   * @return  void
   */
  private static function set_substitutions_v3 (
                                $index,
    SendGridV3\Personalization  $personalization,
    SendGrid\Email              $email_v2
  ) {
    if ( ! is_array( $email_v2->smtpapi->sub ) ) {
      return;
    }

    foreach ( $email_v2->smtpapi->sub as $key => $array_values ) {
      if ( isset( $array_values[ $index ] ) ) {
        $personalization->addSubstitution( $key, $array_values[ $index ] );
      }
    }
  }

  /**
   * Sets the Custom Args (if set) to a V3 Personalization from a V2 Email
   *
   * @param   type  integer                       $index
   * @param   type  SendGridV3\Personalization    $personalization
   * @param   type  SendGrid\Email                $email_v2
   *
   * @return  void
   */
  private static function set_custom_args_v3 (
                                $index,
    SendGridV3\Personalization  $personalization,
    SendGrid\Email              $email_v2
  ) {
    if ( ! is_array( $email_v2->smtpapi->unique_args ) ) {
      return;
    }

    foreach ( $email_v2->smtpapi->unique_args as $key => $array_values ) {
      if ( isset( $array_values[ $index ] ) ) {
        $personalization->addCustomArg( $key, $array_values[ $index ] );
      }
    }
  }

  /**
   * Sets the SendAt for each XSMTPAPI To (if set) to a V3 Personalization from a V2 Email
   * - for API V3 the valus of send_at is a an integer (UNIX Timestamp)
   *
   * @param   type  integer                       $index
   * @param   type  SendGridV3\Personalization    $personalization
   * @param   type  SendGrid\Email                $email_v2
   *
   * @return  void
   */
  private static function set_send_each_at_v3 (
                                $index,
    SendGridV3\Personalization  $personalization,
    SendGrid\Email              $email_v2
  ) {
    if ( ! is_array( $email_v2->smtpapi->send_each_at ) ) {
      return;
    }

    if ( isset( $email_v2->smtpapi->send_each_at[ $index ] ) ) {

      if( is_string( $email_v2->smtpapi->send_each_at[ $index ] ) ) {
        $personalization->setSendAt( intval( trim( $email_v2->smtpapi->send_each_at[ $index ] ) ) );
      } else {
        $personalization->setSendAt( $email_v2->smtpapi->send_each_at[ $index ] );
      }
    }
  }

  /**
   * Sets the SMTPAPI To addresses and ToNames (if set) to a V3 Personalization Object from a V2 Email
   *  - for API V3 at least one recipient (To email address) must be present
   *  - for API V3 the To Name is optional
   *  - SMTPAPI headers have the ToNames in <> brackets, they need to be extracted
   *  - will also set substitution per email
   *  - each SMTPAPI to will have it's own personalization
   *
   * @param   type  SendGridV3\Mail               $email_v3
   * @param   type  SendGrid\Email                $email_v2
   *
   * @return  void
   */
  private static function set_smtpapi_tos_v3 (
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! is_array( $email_v2->smtpapi->to ) ) {
      return;
    }

    foreach ( $email_v2->smtpapi->to as $index => $address ) {
      if ( ! self::is_valid_string( $address ) ) {
        continue;
      }

      $to_name      = null;
      $to_address   = trim( $address );

      // If there is a ToName
      if ( strstr( $address, '<' ) ) {
        // Match for any string followed by any string between <> brackets
        preg_match( '/(.*?)<([^>]+)>/', $address, $output_array );

        // 3nd Grouping (position 2 in array) will be the email address
        if ( isset( $output_array[ 2 ] ) ) {
          $to_address = trim( $output_array[ 2 ] );
        }

        // 2rd Grouping (position 1 in array) will be the ToName
        if ( isset( $output_array[ 1 ] ) ) {
          $to_name = trim( $output_array[ 1 ] );
        }
      }

      // If no <> brackets are found, there should only be one email address
      $recipient = new SendGridV3\Email( $to_name, $to_address );

      // Create a new personalization for this To
      $personalization  = new SendGridV3\Personalization();

      // Add the SMTPAPI Values
      $personalization->addTo( $recipient );
      self::set_substitutions_v3( $index, $personalization, $email_v2 );
      self::set_custom_args_v3( $index, $personalization, $email_v2 );
      self::set_send_each_at_v3( $index, $personalization, $email_v2 );

      // Append the personalization to the email
      $email_v3->addPersonalization( $personalization );
    }
  }

  /**
   * Sets the Categories (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Categories are optional
   *  - for API V3 each category must not exceed 255 characters
   *  - for API V3 you can have no more than 10 categories per request
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_categories_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! is_array( $email_v2->smtpapi->category ) ) {
      return;
    }

    foreach ( $email_v2->smtpapi->category as $index => $category ) {
      $email_v3->addCategory( trim( $category ) );
    }
  }

  /**
   * Sets the Sections (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Sections are optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_sections_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! is_array( $email_v2->smtpapi->section ) ) {
      return;
    }

    foreach ( $email_v2->smtpapi->section as $key => $section ) {
      $email_v3->addSection( $key, $section );
    }
  }

  /**
   * Sets the SendAt (if set) to a V3 Email from a V2 Email
   *  - for API V3 send_at is an integer and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_send_at_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! $email_v2->smtpapi->send_at ) {
      return;
    }

    if ( is_string( $email_v2->smtpapi->send_at ) ) {
      $email_v3->setSendAt( intval( trim( $email_v2->smtpapi->send_at ) ) );
    } else {
      $email_v3->setSendAt( $email_v2->smtpapi->send_at );
    }
  }

  /**
   * Sets the ASM Group ID (if set) to a V3 Email from a V2 Email
   *  - for API V3 the ASM setting is an object and is optional
   *  - for API V3 the ASM group_id is mandatory for each object
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_asm_group_id_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! $email_v2->smtpapi->asm_group_id ) {
      return;
    }

    $asm = new SendGridV3\ASM();
    if ( is_string( $email_v2->smtpapi->asm_group_id ) ) {
      $asm->setGroupId( intval( trim( $email_v2->smtpapi->asm_group_id ) ) );
    } else {
      $asm->setGroupId( $email_v2->smtpapi->asm_group_id );
    }

    $email_v3->setASM( $asm );
  }

  /**
   * Sets the IP Pool Name (if set) to a V3 Email from a V2 Email
   *  - for API V3 the IP Pool Name is a string and is optional
   *  - for API V3 the IP Pool Name must be between 2 and 64 characters in length
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_ip_pool_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    if ( ! is_string( $email_v2->smtpapi->ipPool ) ) {
      return;
    }

    $email_v3->setIpPoolName( $email_v2->smtpapi->ipPool );
  }

  /**
   * Sets the Template ID (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Template ID is a string and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_template_id_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'templates';
    $filter_settings  = array( 'template_id' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    // Fix for wrong label from V2 library
    if ( ! count( $settings ) ) {
      $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings, 'enabled' );
    }

    if ( isset( $settings[ 'template_id' ] ) ) {
      $email_v3->setTemplateId( $settings[ 'template_id' ] );
    }
  }

  /**
   * Sets the BCC Mail Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the BCC Mail Setting is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_bcc_setting_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'bcc';
    $filter_settings  = array( 'email' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'email' ] ) ) {
      $bcc_settings = new SendGridV3\BccSettings();
      $bcc_settings->setEnable( true );
      $bcc_settings->setEmail( $settings[ 'email' ] );

      if ( ! isset( $email_v3->mail_settings ) ) {
        $mail_settings  = new SendGridV3\MailSettings();
        $email_v3->setMailSettings( $mail_settings );
      }

      $email_v3->getMailSettings()->setBccSettings( $bcc_settings );
    }
  }

  /**
   * Sets the Bypass List Management Mail Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Bypass List Management is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_bypass_management_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'bypass_list_management';
    $filter_settings  = array( 'enable' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'enable' ] ) ) {
      $bypass_settings = new SendGridV3\BypassListManagement();
      $bypass_settings->setEnable( true );

      if ( ! isset( $email_v3->mail_settings ) ) {
        $mail_settings  = new SendGridV3\MailSettings();
        $email_v3->setMailSettings( $mail_settings );
      }

      $email_v3->getMailSettings()->setBypassListManagement( $bypass_settings );
    }
  }

  /**
   * Sets the Spam Check Mail Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Spam Check is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_spam_check_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'spamcheck';
    $filter_settings  = array( 'maxscore', 'url' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'maxscore' ] ) or isset( $settings[ 'url' ] ) ) {
      $spamcheck_settings = new SendGridV3\SpamCheck();
      $spamcheck_settings->setEnable( true );

      if ( isset( $settings[ 'maxscore' ] ) ) {
        $spamcheck_settings->setThreshold( $settings[ 'maxscore' ] );
      }

      if ( isset( $settings[ 'url' ] ) ) {
        $spamcheck_settings->setPostToUrl( $settings[ 'url' ] );
      }

      if ( ! isset( $email_v3->mail_settings ) ) {
        $mail_settings  = new SendGridV3\MailSettings();
        $email_v3->setMailSettings( $mail_settings );
      }

      $email_v3->getMailSettings()->setSpamCheck( $spamcheck_settings );
    }
  }

  /**
   * Sets the Email Footer Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Email Footer Setting is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_email_footer_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'footer';
    $filter_settings  = array( 'text/html', 'text/plain' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'text/html' ] ) or isset( $settings[ 'text/plain' ] ) ) {
      $footer_settings = new SendGridV3\Footer();
      $footer_settings->setEnable( true );

      if ( isset( $settings[ 'text/html' ] ) ) {
        $footer_settings->setHtml( $settings[ 'text/html' ] );
      }

      if ( isset( $settings[ 'text/plain' ] ) ) {
        $footer_settings->setText( $settings[ 'text/plain' ] );
      }

      if ( ! isset( $email_v3->mail_settings ) ) {
        $mail_settings  = new SendGridV3\MailSettings();
        $email_v3->setMailSettings( $mail_settings );
      }

      $email_v3->getMailSettings()->setFooter( $footer_settings );
    }
  }

  /**
   * Sets the Click Tracking Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Click Tracking Setting is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_click_tracking_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'clicktrack';
    $filter_settings  = array( 'enable' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'enable' ] ) ) {
      $click_tracking_settings = new SendGridV3\ClickTracking();
      $click_tracking_settings->setEnable( true );
      $click_tracking_settings->setEnableText( true );

      if ( ! isset( $email_v3->tracking_settings ) ) {
        $tracking_setings  = new SendGridV3\TrackingSettings();
        $email_v3->setTrackingSettings( $tracking_setings );
      }

      $email_v3->getTrackingSettings()->setClickTracking( $click_tracking_settings );
    }
  }

  /**
   * Sets the Open Tracking Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Open Tracking Setting is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_open_tracking_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'opentrack';
    $filter_settings  = array( 'enable', 'replace' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'enable' ] ) and isset( $settings[ 'replace' ] ) ) {
      $open_tracking_settings = new SendGridV3\OpenTracking();
      if ( $settings[ 'enable' ] ) {
        $open_tracking_settings->setEnable( true );
      }

      $open_tracking_settings->setSubstitutionTag( $settings[ 'replace' ] );

      if ( ! isset( $email_v3->tracking_settings ) ) {
        $tracking_setings  = new SendGridV3\TrackingSettings();
        $email_v3->setTrackingSettings( $tracking_setings );
      }

      $email_v3->getTrackingSettings()->setOpenTracking( $open_tracking_settings );
    }
  }

  /**
   * Sets the Subscription Tracking Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Subscription Tracking Setting is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_subscription_tracking_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'subscriptiontrack';
    $filter_settings  = array( 'enable', 'replace', 'text/html', 'text/plain' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'enable' ] ) ) {
      $subscription_tracking_settings = new SendGridV3\SubscriptionTracking();

      if ( $settings[ 'enable' ] ) {
        $subscription_tracking_settings->setEnable( true );
      }
      
      if( isset( $settings[ 'replace' ] ) ) {
        $subscription_tracking_settings->setSubstitutionTag( $settings[ 'replace' ] );
      }

      if( isset( $settings[ 'text/html' ] ) ) {
        $subscription_tracking_settings->setHtml( $settings[ 'text/html' ] );
      }

      if( isset( $settings[ 'text/plain' ] ) ) {
        $subscription_tracking_settings->setText( $settings[ 'text/plain' ] );
      }

      if ( ! isset( $email_v3->tracking_settings ) ) {
        $tracking_setings  = new SendGridV3\TrackingSettings();
        $email_v3->setTrackingSettings( $tracking_setings );
      }

      $email_v3->getTrackingSettings()->setSubscriptionTracking( $subscription_tracking_settings );
    }
  }

  /**
   * Sets the Google Analytics Tracking Setting (if set) to a V3 Email from a V2 Email
   *  - for API V3 the Google Analytics Tracking Setting is an object and is optional
   *
   * @param   type  SendGridV3\Mail   $email_v3
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  void
   */
  private static function set_ganalytics_v3(
    SendGridV3\Mail   $email_v3,
    SendGrid\Email    $email_v2
  ) {
    $filter_key       = 'ganalytics';
    $filter_settings  = array( 'enable', 'utm_source', 'utm_medium', 'utm_term', 'utm_content', 'utm_campaign' );

    $settings = self::get_smtp_filter_settings( $email_v2, $filter_key, $filter_settings );

    if ( isset( $settings[ 'enable' ] ) ) {
      $ganalytics_tracking_settings = new SendGridV3\Ganalytics();

      if ( $settings[ 'enable' ] ) {
        $ganalytics_tracking_settings->setEnable( true );
      }
      
      if( isset( $settings[ 'utm_source' ] ) ) {
        $ganalytics_tracking_settings->setCampaignSource( $settings[ 'utm_source' ] );
      }

      if( isset( $settings[ 'utm_medium' ] ) ) {
        $ganalytics_tracking_settings->setCampaignMedium( $settings[ 'utm_medium' ] );
      }

      if( isset( $settings[ 'utm_term' ] ) ) {
        $ganalytics_tracking_settings->setCampaignTerm( $settings[ 'utm_term' ] );
      }

      if( isset( $settings[ 'utm_content' ] ) ) {
        $ganalytics_tracking_settings->setCampaignContent( $settings[ 'utm_content' ] );
      }

      if( isset( $settings[ 'utm_campaign' ] ) ) {
        $ganalytics_tracking_settings->setCampaignName( $settings[ 'utm_campaign' ] );
      }

      if ( ! isset( $email_v3->tracking_settings ) ) {
        $tracking_setings  = new SendGridV3\TrackingSettings();
        $email_v3->setTrackingSettings( $tracking_setings );
      }

      $email_v3->getTrackingSettings()->setGanalytics( $ganalytics_tracking_settings );
    }
  }

  /**
   * Returns a JSON encoded object for an API V3 mail send request, 
   *  from a V2 SendGrid Email object (v2 library).
   *
   * @param   type  SendGrid\Email    $email_v2
   *
   * @return  string
   */
  public static function to_api_v3( SendGrid\Email $email_v2 ) {
    // Initialization
    $email_v3 = new SendGridV3\Mail();
    
    // Standard fields transformation
    self::set_from_v3( $email_v3, $email_v2 );
    self::set_subject_v3( $email_v3, $email_v2 );
    self::set_text_content_v3( $email_v3, $email_v2 );
    self::set_html_content_v3( $email_v3, $email_v2 );
    self::set_reply_to_v3( $email_v3, $email_v2 );
    self::set_headers_v3( $email_v3, $email_v2 );
    self::set_attachments_v3( $email_v3, $email_v2 );

    // XSMTPAPI Standard transformations
    self::set_categories_v3( $email_v3, $email_v2 );
    self::set_sections_v3( $email_v3, $email_v2 );
    self::set_send_at_v3( $email_v3, $email_v2 );
    self::set_asm_group_id_v3( $email_v3, $email_v2 );
    self::set_ip_pool_v3( $email_v3, $email_v2 );
    
    // Mail Settings
    self::set_template_id_v3( $email_v3, $email_v2 );
    self::set_bcc_setting_v3( $email_v3, $email_v2 );
    self::set_bypass_management_v3( $email_v3, $email_v2 );
    self::set_spam_check_v3( $email_v3, $email_v2 );
    self::set_email_footer_v3( $email_v3, $email_v2 );

    // Tracking settings
    self::set_click_tracking_v3( $email_v3, $email_v2 );
    self::set_open_tracking_v3( $email_v3, $email_v2 );
    self::set_subscription_tracking_v3( $email_v3, $email_v2 );
    self::set_ganalytics_v3( $email_v3, $email_v2 );

    // Exclusive Tos
    self::set_smtpapi_tos_v3( $email_v3, $email_v2 );

    // Personalization transformation
    if ( ! is_array( $email_v3->personalization ) or
      count( $email_v3->personalization ) == 0 ) {
      self::set_tos_v3( $email_v3, $email_v2 );
    }

    // Set the CCs and BCCs to the first To
    if ( is_array( $email_v3->personalization ) and 
      isset( $email_v3->personalization[0] ) ) {
      self::set_ccs_v3( $email_v3->personalization[0], $email_v2 );
      self::set_bccs_v3( $email_v3->personalization[0], $email_v2 );
    }

    // Return API v3 formatted JSON
    return json_encode( $email_v3 );
  }
}