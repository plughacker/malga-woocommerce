<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="malgapayments-credit-form" class="malgapayments-method-form">     
    <p id="malgapayments-card-holder-name-field" class="form-row form-row-first">
        <label for="malgapayments-card-holder-name"><?php esc_html_e( 'Card Holder Name', 'malga-payments-gateway' ); ?> <small>(<?php esc_html_e( 'as recorded on the card', 'malga-payments-gateway' ); ?>)</small> <span class="required">*</span></label>
        <input id="malgapayments-card-holder-name" name="malgapayments_card_holder_name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
    </p>
    <p id="malgapayments-card-number-field" class="form-row form-row-last">
        <label for="malgapayments-card-number"><?php esc_html_e( 'Card Number', 'malga-payments-gateway' ); ?> <span class="required">*</span></label>
        <input id="malgapayments-card-number" name="malgapayments_card_number" class="input-text wc-credit-card-form-card-number" type="tel" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
    </p>
    <div class="clear"></div>
    <p id="malgapayments-card-expiry-field" class="form-row form-row-first">
        <label for="malgapayments-card-expiry"><?php esc_html_e( 'Expiry (MM/YYYY)', 'malga-payments-gateway' ); ?> <span class="required">*</span></label>
        <input id="malgapayments-card-expiry" name="malgapayments_card_expiry" class="input-text wc-credit-card-form-card-expiry" type="tel" autocomplete="off" placeholder="<?php esc_html_e( 'MM / YYYY', 'malga-payments-gateway' ); ?>" style="font-size: 1.5em; padding: 8px;" />
    </p>
    <p id="malgapayments-card-cvc-field" class="form-row form-row-last">
        <label for="malgapayments-card-cvc"><?php esc_html_e( 'Security Code', 'malga-payments-gateway' ); ?> <span class="required">*</span></label>
        <input id="malgapayments-card-cvc" name="malgapayments_card_cvv" class="input-text wc-credit-card-form-card-cvv" type="tel" autocomplete="off" placeholder="<?php esc_html_e( 'CVV', 'malga-payments-gateway' ); ?>" style="font-size: 1.5em; padding: 8px;" />
    </p>
    <div class="clear"></div>
    <p id="malgapayments-card-installments-field" class="form-row form-row-first">
        <label for="malgapayments-card-installments">
            <?php esc_html_e( 'Installments', 'malga-payments-gateway' ); ?><span class="required">*</span><br />
            <?php
                $minimum_value = $minimum_installment;
                switch ($currency) {
                    case 'USD':
                        $minimum_value = '$'.number_format( $minimum_installment, 2, '.', ',' );
                        break;
                    case 'EUR':
                        $minimum_value = '€'.number_format( $minimum_installment, 2, '.', ',' );
                        break;
                    default:
                        $minimum_value = 'R$ '.number_format( $minimum_installment, 2, ',', '.' );
                        break;
                }
            ?>
            <small><?php echo sprintf(__( 'the minimum value of the installment is %s.', 'malga-payments-gateway' ), esc_attr($minimum_value)); ?></small>
        </label>
        <select id="malgapayments-card-installments" name="malgapayments_card_installments" style="font-size: 1.5em; padding: 4px; width: 100%;">
            <?php 
                $allowedHTML = ['option' => ['value' => []]];
                $installments = floor($cart_total / $minimum_installment); 

                for ($i = 1; $i <= $installments; $i++) {
                    if($i <= $maximum_installment){                        
                        switch ($currency) {
                            case 'USD':
                                $installments_amount = number_format( ($cart_total / $i), 2, '.', ',' );
                                echo wp_kses("<option value='$i'>$i x ($$installments_amount)</option>", $allowedHTML);
                                break;
                            case 'EUR':
                                $installments_amount = number_format( ($cart_total / $i), 2, '.', ',' );
                                echo wp_kses("<option value='$i'>$i x (€$installments_amount)</option>", $allowedHTML);
                                break;
                            default:
                                $installments_amount = number_format( ($cart_total / $i), 2, ',', '.' );
                                echo wp_kses("<option value='$i'>$i x (R$ $installments_amount)</option>", $allowedHTML);
                                break;
                        }
                    }
                }
            ?>            
        </select>
    </p>
    <div class="clear"></div>
</div>