<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$this->form_fields["boleto_expires"] = array(
    'title'       => __( 'Expires days', 'malga-payments-gateway' ),
    'type'        => 'number',
    'label_class' => 'mishaclass',
    'description' => __( 'Enter the number of days for the boleto to expire', 'malga-payments-gateway' ),
    'default'     => '5',
);

$this->form_fields["boleto_instructions"] = array(
    'title'       => __( 'Instructions', 'malga-payments-gateway' ),
    'type'        => 'text',
    'description' => __( 'Please enter your instructions for Boleto', 'malga-payments-gateway' ),
    'desc_tip'    => true,
    'default'     => 'Instruções para pagamento do boleto',
);

$this->form_fields["interest_type"] = array(
    'title'       => __( 'Interest', 'malga-payments-gateway' ),
    'type'        => 'select',
    'description' => __( 'Enter the format to interest', 'malga-payments-gateway' ),
    'options'     => array(
        'amount' => __('Amount', 'malga-payments-gateway' ),
        'percentage' => __('Percentage', 'malga-payments-gateway' )
    ),
);
$this->form_fields["interest_value"] = array(
    'type'        => 'number',
    'description' => __( 'Enter value for interest', 'malga-payments-gateway' ),
    'default'     => '1'
);
$this->form_fields["interest_days"] = array(
    'type'        => 'number',
    'description' => __( 'Enter days for interest', 'malga-payments-gateway' ),
    'default'     => '5'
);


$this->form_fields["fine_type"] = array(
    'title'       => __( 'Fine', 'malga-payments-gateway' ),
    'type'        => 'select',
    'description' => __( 'Enter the format to fine', 'malga-payments-gateway' ),
    'options'     => array(
        'amount' => __('Amount', 'malga-payments-gateway' ),
        'percentage' => __('Percentage', 'malga-payments-gateway' )
    ),
);
$this->form_fields["fine_value"] = array(
    'type'        => 'number',
    'description' => __( 'Enter value for fine', 'malga-payments-gateway' ),
    'default'     => '1'
);
$this->form_fields["fine_days"] = array(
    'type'        => 'number',
    'description' => __( 'Enter days for fine', 'malga-payments-gateway' ),
    'default'     => '5'
);