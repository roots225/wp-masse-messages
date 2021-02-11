<?php
if (!defined('ABSPATH')) {
  exit;
}
// require bulkgate package for sending sms
// we must replace this by masse-message api in future
require_once MASSE_MESSAGES_PLUGIN_ABSPATH.'/vendor/autoload.php';
use BulkGate\Sms\SenderSettings;

if ( ! class_exists( 'LordCros_Core_Room_Booking' ) ) {
	class LordCros_Core_Room_Booking {
		public $booking_id = '';

		public function __construct( $booking_id ) {
			$this->booking_id = $booking_id; 
		}

		public function get_booking_info() {
			global $wpdb;

			if ( empty( $this->booking_id ) ) {
				return false;
			}

			$sql = $wpdb->prepare( 'SELECT lordcros_booking.* FROM ' . LORDCROS_ROOM_BOOKINGS_TABLE . ' AS lordcros_booking WHERE lordcros_booking.id=%s', $this->booking_id );
			$booking_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $booking_data ) ) {
				return false;
			}

			return $booking_data;
		}		
	}
}


if (!function_exists('masse_messages_send_confirmation_sms')) {
  function masse_messages_send_confirmation_sms ($order_id) {
		global $wpdb;

		$keyFile = fopen(MASSE_MESSAGES_PLUGIN_ABSPATH.'/key', 'r');
		$apiKeys = [];
		while (!feof($keyFile)) {
			$line = fgets($keyFile);
			list($key, $value) = explode(':', $line);
			$apiKeys[$key] = $value;
		}
		$apiKeys = (object) $apiKeys;
		
		$connection = new BulkGate\Message\Connection($apiKeys->appid, $apiKeys->token);

    $type = SenderSettings\Gate::GATE_TEXT_SENDER;
    $value = 'ESPOIRLODGE';
    $settings = new SenderSettings\StaticSenderSettings($type, $value);

    $order = new LordCros_Core_Room_Booking( $order_id );
    $order_data = $order->get_booking_info();

		if ( empty( $order_data ) ) {
			return false;
		}

		// // server variables
		$admin_email = get_option( 'admin_email' );
		$home_url = esc_url( home_url( '/' ) );
		$site_name = filter_input( INPUT_SERVER, 'SERVER_NAME' );

		$logo_uploaded = lordcros_get_opt( 'alternative_logo' );

		if ( isset( $logo_uploaded['url'] ) ) {
			$logo_url = $logo_uploaded['url'];
		} else {
			$logo_url = '';
		}

		$order_data['room_id'] = lordcros_core_room_clang_id( $order_data['post_id'] );

		// room info
		$room_name = get_the_title( $order_data['room_id'] );
		$room_url = esc_url( lordcros_core_get_permalink_clang( $order_data['room_id'] ) );
		$room_thumbnail = get_the_post_thumbnail( $order_data['room_id'], 'medium' );
		$address = lordcros_get_opt( 'address' );
		$phone = lordcros_get_opt( 'phone_num_val' );
		$email = lordcros_get_opt( 'email_address' );

		// booking info
		$date_from = new DateTime( $order_data['date_from'] );
		$date_to = new DateTime( $order_data['date_to'] );
		$number1 = $date_from->format( 'U' );
		$number2 = $date_to->format( 'U' );
		$booking_nights = ( $number2 - $number1 ) / ( 3600 * 24 );
		$from_date = date_i18n( 'j F Y', strtotime( $order_data['date_from'] ) );
		$to_date = date_i18n( 'j F Y', strtotime( $order_data['date_to'] ) );
		$adults = $order_data['adults'];
		$kids = $order_data['kids'];
		$total_price = lordcros_price( $order_data['total_price'] );
		$discounted_price = lordcros_price( $order_data['discounted_price'] );
		$room_price = lordcros_price( $order_data['room_price'] );
		$service_price = lordcros_price( $order_data['service_price'] );
		$coupon_code = $order_data['coupon_code'];
		$booking_payment_type = $order_data['payment'];
		$statuses = array( 'inquiry' => esc_html__( 'Just Request', 'lordcros-core' ), 'paypal' => esc_html__( 'Paypal', 'lordcros-core' ), 'stripe' => esc_html__( 'Stripe', 'lordcros-core' ) );
		if ( isset( $statuses[$order_data['payment']] ) ) {
			$booking_payment_type = $statuses[$order_data['payment']];
		}
		$transaction_id = $order_data['transaction_id'];
		$booking_status = $order_data['status'];

		$extra_service = '';
		if ( ! empty( $order_data['extra_service'] ) ) {
			$booked_extra_services = unserialize( $order_data['extra_service'] );
			$args = array(
					'posts_per_page'	=> -1,
					'post_type'			=> 'room_service',
					'post_status'		=> 'publish',
					'post__in'			=> $booked_extra_services
				);
			$extra_services = get_posts( $args );

			$booked_extra_services = array();
			if ( ! empty( $extra_services ) ) {
				foreach ( $extra_services as $e_service ) {
					$booked_extra_services[] = $e_service->post_title;
				}
			}
			$extra_service = implode( ', ', $booked_extra_services );
		}

		// customer info
		$customer_first_name = $order_data['first_name'];
		$customer_last_name = $order_data['last_name'];
		$customer_phone = $order_data['phone'];
		$customer_email = $order_data['email'];
		$customer_country_code = $order_data['country'];
		$customer_address1 = $order_data['address1'];
		$customer_address2 = $order_data['address2'];
		$customer_city = $order_data['city'];
		$customer_zip = $order_data['zip'];
		$customer_country = $order_data['country'];
		$arrival = $order_data['arrival'];
    $customer_special_requirements = $order_data['special_requirements'];

    // $customer_first_name = 'Yao';
		// $customer_last_name = 'Gilles';
		// $customer_phone = '09779639';
    
    $sender = new BulkGate\Sms\Sender($connection);
		$sender->setSenderSettings($settings);
		$sender->setDefaultCountry($customer_country);
		$isValidPhone = $sender->checkPhoneNumbers($customer_phone, $customer_country);

    ob_start();
    echo 'Bonjour '. $customer_first_name . ' ' . $customer_last_name .' ';
    echo 'Votre reservation a bien ete prise en compte.';
    $text = ob_get_clean();
    $length = strlen($text);
    $size = (int) ceil($length / 160);
    
    if (class_exists('MessageMessagesCore') && $isValidPhone) {
      $message = new BulkGate\Sms\Message($customer_phone, $text);
      if (MessageMessagesCore::getSmsAccount() && (MessageMessagesCore::getSmsAccount() >= $size)) {
        $response = $sender->send($message);
        // MessageMessagesCore::updateSmsAccount($size);
      } else {
        $subject = "Vous ne disposez plus de sms";
        $description = "SMS non envoyé lors d'une réservation";
        if (function_exists('lordcros_core_send_mail')) {
          lordcros_core_send_mail( $site_name, $admin_email, $admin_email, $subject, $description );
        }
      }
    } else {
			$subject = "Numero de téléphone non valide";
			$description = "SMS non envoyé lors d'une réservation";
			if (function_exists('lordcros_core_send_mail')) {
				lordcros_core_send_mail( $site_name, $admin_email, $admin_email, $subject, $description );
			}
		}

  }
  
  add_action('lordcros_room_send_confirmation_email', 'masse_messages_send_confirmation_sms');
  add_action('wp_ajax_send_confirmation_sms', 'masse_messages_send_confirmation_sms');
}
