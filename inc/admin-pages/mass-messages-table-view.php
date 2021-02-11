<?php
/**
 * Masse Message Plugin table view render pages
 */

if (!function_exists('masse_messages_table_render_view_pages')) {
  function masse_messages_table_render_view_pages () {
    $account = MessageMessagesCore::getSmsAccount();
    ?>
    <style>
      .container {
        width: 80%;
      }
      .text-danger {
        color: red;
      }
      .text-right {
        text-align: right;
      }
    </style>
    <div class="container">
      <h1>Bienvenue sur votre espace administration des SMS</h1>
      <div class="">Vous avez <span class="text-danger"><?= $account ?> SMS restant</span></div>
      <button class="btn btn-primary" id="sendSms">send</button>
    </div>
    <script type="text/javascript">
      document.addEventListener('DOMContentLoaded', function () {
        jQuery('#sendSms').click(function(e){
          e.preventDefault();
          jQuery.post(ajaxurl, {action: 'send_confirmation_sms'}, function(response){
            console.log(response)
          })
        })
      })
    </script>
    <?php
  }
}