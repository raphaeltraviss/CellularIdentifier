<?php

use Skyleaf\CellularIdentifier\CellularIdentifier;
use Skyleaf\CellularIdentifier\Specification;
use Skyleaf\CellularIdentifier\Format;

class ConversionTest extends PHPUnit_Framework_TestCase {

  public function testHexDecConversion() {
    $known_device = array_rand($this->exampleDevices);
    $known_identifier = array_rand($this->exampleDevices[$known_device]);
    $random_identifier = $this->exampleDevices[$known_device][$known_identifier];

    $identifier = new CellularIdentifier($random_identifier);
    echo($identifier->specification());
    echo($identifier->format());
    echo($identifier->value());
    $specification = $identifier->specification();

    $identifier->hex();
    $hex_value = $identifier->value();


    $identifier->dec();
    $dec_value = $identifier->value();

    // Hex fails for UTStarcom example with all-decimal hex digits.
    $this->assertEquals(strtoupper($this->exampleDevices[$known_device][$specification . 'hex']), $hex_value);
    $this->assertEquals($this->exampleDevices[$known_device][$specification . 'dec'], $dec_value);

  }

  // IMEI should not convert to MEID decimal.

  // IMEI->hex() should not change the format.

  // ESN->meid should not change the specification.

  // IMEI->MEID or ESN should not change the specification.






  // Private nternal state.






  /**
   * A list of imaginary devices, with pre-calculated values.
   *
   * In order to accurately test this libary agains actual device identifiers, these
   * made-up values are inside the valid manufacturer prefix and serial number ranges,
   * meaning that they could intersect with an actual device, somewhere: if this
   * occurs, it is purely coincidental.  Unfortunaley the MEID specification does
   * not define any ranges for testing and examples. Filled during initialization.
   */
  protected $exampleDevices = array();








  // Magic methods.







  public function __construct() {
    parent::__construct();

    // PHP 5.3 array key workaround when using concatenation to form array keys.
    $device_array_keys = array(
      Specification::MEID . Format::hexadecimal,
      Specification::MEID . Format::decimal,
      Specification::ESN . Format::hexadecimal,
      Specification::ESN . Format::decimal
    );

    $starcom_values = array(
      '99000001123456', // Tests all-decimal MEID.
      '256691404901193046',
      '80C06296',
      '12812608150'
    );
    $starcom_device = array_combine($device_array_keys, $starcom_values);

    $samsung_values = array(
      'A10000004F1A0C',
      '270113177605184012',
      '8017659D',
      '12801533341'
    );
    $samsung_device = array_combine($device_array_keys, $samsung_values);

    $lg_values = array(
      'A000000c1124aC',
      '268435457201123500',
      '803f3D13',  // Tests input with mixed-case.
      '12804144403'
    );
    $lg_device = array_combine($device_array_keys, $lg_values);

    $apple_values = array(
      '35695706001456',
      '089609600600005206',
      '80AAD01F',
      '12811194399'
    );
    $apple_device = array_combine($device_array_keys, $apple_values);

    $this->exampleDevices = array($starcom_device, $samsung_device, $lg_device, $apple_device);
  }
}
