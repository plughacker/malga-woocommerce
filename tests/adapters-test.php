<?php
require "tests/mocks/api.php";
require "tests/mocks/order.php";
require "tests/mocks/functions.php";

if(is_dir("src"))
  require "src/plugins/woocommerce-malga-payments/includes/class-malga-charges-adapter.php";
else
  require "wp-content/plugins/woocommerce-malga-payments/includes/class-malga-charges-adapter.php";

use PHPUnit\Framework\TestCase;

/**
 * @covers Malga_Charges_Adapter::
 */
class AdaptersTest extends TestCase{
  /**
  * @covers MAlga_Charges_Adapter::to_credit
  */  
  public function testCredit(){
    $input = json_decode( file_get_contents("tests/payloads/credit/input.json"), true);
    $output = json_decode( file_get_contents("tests/payloads/credit/output.json"), true);
    $_POST = $input

    $adapter = new Malga_Charges_Adapter( new MockAPI(), new MockOrder());

    $adapter->to_credit($input);

    $this->assertEquals($adapter->payload, $output);
  }

  /**
  * @covers Malga_Charges_Adapter::to_pix
  */    
  public function testPix(){
    $input = json_decode( file_get_contents("tests/payloads/pix/input.json"), true);
    $output = json_decode( file_get_contents("tests/payloads/pix/output.json"), true);
    $_POST = $input

    $adapter = new Malga_Charges_Adapter( new MockAPI(), new MockOrder());

    $adapter->to_pix($input);

    $this->assertEquals($adapter->payload, $output);
  }

  /**
  * @covers Malga_Charges_Adapter::to_boleto
  */  
  public function testBoleto(){    
    $input = json_decode( file_get_contents("tests/payloads/boleto/input.json"), true);
    $output = json_decode( file_get_contents("tests/payloads/boleto/output.json"), true);
    $_POST = $input

    $output['paymentMethod']['expiresDate'] = date('Y-m-d', strtotime('+5 days'));
   
    $adapter = new Malga_Charges_Adapter( new MockAPI(), new MockOrder());

    $adapter->to_boleto($input);

    $this->assertEquals($adapter->payload, $output);
  }
}
