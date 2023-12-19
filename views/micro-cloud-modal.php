<?php

use LLAR\Core\Config;

if( !defined( 'ABSPATH' ) ) exit();

/**
 * @var $this LLAR\Core\LimitLoginAttempts
 */

$setup_code = Config::get( 'app_setup_code' );
//if( !empty( $setup_code ) ) return;

$admin_email = ( !is_multisite() ) ? get_option( 'admin_email' ) : get_site_option( 'admin_email' );
$url_site = esc_url(get_site_url());

ob_start(); ?>
    <div class="micro_cloud_modal__content">
        <div class="micro_cloud_modal__body">
            <div class="micro_cloud_modal__body_header">
                <div class="left_side">
                    <div class="title">
                        <?php _e( 'Get Started with Micro Cloud for FREE', 'limit-login-attempts-reloaded' ); ?>
                    </div>
                    <div class="description">
                        <?php _e( 'Help us secure our network and we’ll provide you with limited access to our premium features including our login firewall, IP Intelligence, and performance optimizer.', 'limit-login-attempts-reloaded' ); ?>
                    </div>
                    <div class="description-add">
                        <?php _e( 'Please note that some domains have very high brute force activity, which may cause Micro Cloud to run out of resources in under 24 hours. We will send an email when resources are fully utilized and the app reverts back to the free version. You may upgrade to one of our premium plans to prevent the app from reverting.', 'limit-login-attempts-reloaded' ); ?>
                    </div>
                </div>
                <div class="right_side">
                    <img src="<?php echo LLA_PLUGIN_URL ?>assets/css/images/micro-cloud-image-min.png">
                </div>
            </div>
            <div class="card mx-auto">
                <div class="card-header">
                    <div class="title">
                        <img src="<?php echo LLA_PLUGIN_URL ?>assets/css/images/tools.png">
                        <?php _e( 'How To Activate Micro Cloud', 'limit-login-attempts-reloaded' ); ?>
                    </div>
                </div>
                <div class="card-body step-first">
                    <div class="url_site">
                        <?php echo sprintf(__( 'Site URL: <a href="%s" class="link__style_unlink llar_orange">%s</a>', 'limit-login-attempts-reloaded' ), $url_site, $url_site); ?>
                    </div>
                    <div class="description">
                        <?php _e( 'Please enter the email that will receive setup instructions', 'limit-login-attempts-reloaded' ); ?>
                    </div>
                    <div class="field-wrap">
                        <div class="field-email">
                            <input type="text" class="input_border" id="llar-subscribe-email" placeholder="Your email" value="<?php esc_attr_e( $admin_email ); ?>">
                        </div>
                    </div>
                    <div class="button_block-single">
                        <button class="button menu__item button__orange" id="llar-button_subscribe-email">
                            <?php _e( 'Continue', 'limit-login-attempts-reloaded' ); ?>
                            <span class="preloader-wrapper"><span class="spinner llar-app-ajax-spinner"></span></span>
                        </button>
                        <div class="description_add">
                            <?php _e( 'By signing up you agree to our terms of service and privacy policy.', 'limit-login-attempts-reloaded' ); ?>
                        </div>
                    </div>
                </div>
                <div class="card-body step-second llar-display-none">
                    <div class="llar-upgrade-subscribe_notification__error llar-display-none">
                        <img src="<?php echo LLA_PLUGIN_URL ?>assets/css/images/start.png">
                        <?php _e( 'The server is not working, try again later', 'limit-login-attempts-reloaded' ); ?>
                    </div>
                    <div class="llar-upgrade-subscribe_notification">
                        <div class="field-image">
                            <img src="<?php echo LLA_PLUGIN_URL ?>assets/css/images/schema-ok-min.png">
                        </div>
                        <div class="field-desc">
                            <?php _e( 'This email will receive notifications of unauthorized access to your website. You may turn this off in your settings.', 'limit-login-attempts-reloaded' ); ?>
                        </div>
                    </div>
                    <div class="button_block-single">
                        <button class="button next_step menu__item button__orange" id="llar-button_dashboard">
                            <?php _e( 'Go To Dashboard', 'limit-login-attempts-reloaded' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$micro_cloud_popup_content = ob_get_clean();
?>

<script>
    ;(function($){

        $(document).ready(function(){

            const $button_micro_cloud = $('.button.button_micro_cloud, a.button_micro_cloud');
            const sec_app_setup = '<?php echo esc_js( wp_create_nonce( "llar-app-setup" ) ); ?>';

            $button_micro_cloud.on('click', function () {
                micro_cloud_modal.open();
            })

            const micro_cloud_modal = $.dialog({
                title: false,
                content: `<?php echo trim( $micro_cloud_popup_content ); ?>`,
                lazyOpen: true,
                type: 'default',
                typeAnimated: true,
                draggable: false,
                animation: 'top',
                animationBounce: 1,
                offsetTop: 50,
                boxWidth: 1280,
                bgOpacity: 0.9,
                useBootstrap: false,
                closeIcon: true,
                buttons: {},
                onOpenBefore: function () {

                    const $subscribe_email = $('#llar-subscribe-email');
                    const $button_subscribe_email = $('#llar-button_subscribe-email');
                    const $card_body_first = $('.card-body.step-first');
                    const $card_body_second = $('.card-body.step-second');
                    const $button_dashboard = $('#llar-button_dashboard');
                    const $subscribe_notification = $('.llar-upgrade-subscribe_notification');
                    const $subscribe_notification_error = $('.llar-upgrade-subscribe_notification__error');
                    const $spinner = $button_subscribe_email.find('.preloader-wrapper .spinner');
                    const disabled = 'llar-disabled';
                    const visibility = 'llar-visibility';
                    let real_email = '<?php esc_attr_e( $admin_email ); ?>';

                    $subscribe_email.on('blur', function() {

                        let email = $(this).val().trim();

                        if (!is_valid_email(email)) {
                            $button_subscribe_email.addClass(disabled)
                        }
                        else {
                            $button_subscribe_email.removeClass(disabled)
                            real_email = email;
                        }
                    });

                    $button_subscribe_email.on('click', function (e) {
                        e.preventDefault();

                        if($button_subscribe_email.hasClass(disabled)) {
                            return;
                        }

                        $button_subscribe_email.addClass(disabled);
                        $spinner.addClass(visibility);

                        activate_micro_cloud(real_email)
                            .then(function(response) {

                                $button_subscribe_email.removeClass(disabled);
                            })
                            .catch(function() {

                                $subscribe_notification_error.removeClass('llar-display-none');
                                $subscribe_notification.addClass('llar-display-none');
                            })
                            .finally(function() {

                                $card_body_first.addClass('llar-display-none');
                                $card_body_second.removeClass('llar-display-none');
                            });

                        $button_dashboard.on('click', function () {
                            window.location = window.location + '&tab=dashboard';

                        })

                    })
                }
            });
        })

    })(jQuery)
</script>