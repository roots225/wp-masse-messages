<?php
/**
 * Plugin Name: Masse Messages
 * Plugin URI: http://masse-messages.com
 * Description: Send marketing sms to customer
 * Version: 1.0.0
 * Author: Gilles Yao
 * Text Domain: masse-messages
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

define('MASSE_MESSAGES_PLUGIN_ABSPATH', dirname( __FILE__ ));
define('MASSE_MESSAGES_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('MASSE_MESSAGES_EXP_TEXT', 'ESPOIRLODGE');
define('MASSE_MESSAGES_FILE_DB_URL', dirname(__FILE__).'/data');

class MessageMessagesCore {
  public $version = '1.0';

  public function __construct() {
    $this->includes();

    $this->textDomainInit();
  }

  private function textDomainInit () {
    load_plugin_textdomain('masse-messages', false, MASSE_MESSAGES_PLUGIN_ABSPATH. '/languages/masse-messages.pot');
  }

  private function includes () {
    require_once(MASSE_MESSAGES_PLUGIN_ABSPATH . '/inc/admin-pages/main.php');
    require_once(MASSE_MESSAGES_PLUGIN_ABSPATH . '/inc/sms/main.php');
  }

  public static function updateSmsAccount (int $size) {
    $account = self::getSmsAccount();
    
    if ($account > 0) $account -= $size;
    $writer = fopen(MASSE_MESSAGES_FILE_DB_URL, 'w+');
    if ($writer) {
      fwrite($writer, $account);
    }
    fclose($writer);
  }

  public static function getSmsAccount () {
    $account = 0;
    $reader = fopen(MASSE_MESSAGES_FILE_DB_URL, 'r');
    if ($reader) {
      $account = fgets($reader);
      if ($account !== false) {
        $account = (int) $account;
      }
    }
    fclose($reader);

    return $account;
  }
}

new MessageMessagesCore();