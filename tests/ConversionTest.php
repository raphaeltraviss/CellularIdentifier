<?php

use Skyleaf\CellularIdentifier\CellularIdentifier;
use Skyleaf\CellularIdentifier\Specification;
use Skyleaf\CellularIdentifier\Format;

class ConversionTest extends PHPUnit_Framework_TestCase {

  // A list of imaginary devices, with pre-calculated values.
  // In order to accurately test this libary agains actual device identifiers, these
  // made-up values are inside the valid manufacturer prefix and serial number ranges,
  // meaning that they could intersect with an actual device, somewhere: if this
  // occurs, it is purely coincidental.  Unfortunaley the MEID specification does
  // not define any ranges for testing and examples.
  protected $exampleDevices = array(
    // An example of a UTStarcom identifier.
    array(
      Specification::MEID . Format::hexadecimal => '99000001123456',
      Specification::MEID . Format::decimal => '256691404901193046',
      Specification::ESN . Format::hexadecimal => '80C06296',
      Specification::ESN . Format::decimal => '12812608150'
    ),

    // An example of a Samsung identifier.
    array(
      Specification::MEID . Format::hexadecimal => 'A10000004F1A0C',
      Specification::MEID . Format::decimal => '270113177605184012',
      Specification::ESN . Format::hexadecimal => '8017659D',
      Specification::ESN . Format::decimal => '12801533341'
    ),

    // An example of an LG Electronics identifier.
    // Test mixed-case hexadecimal digits.
    array(
      Specification::MEID . Format::hexadecimal => 'A000000c1124aC',
      Specification::MEID . Format::decimal => '268435457201123500',
      Specification::ESN . Format::hexadecimal => '803f3D13',
      Specification::ESN . Format::decimal => '12804144403'
    ),

    // An example of an LG Electronics identifier.
    array(
      Specification::MEID . Format::hexadecimal => '99000001abcdef',
      Specification::MEID . Format::decimal => '256691404911259375',
      Specification::ESN . Format::hexadecimal => '80AE567C',
      Specification::ESN . Format::decimal => '12811425404'
    ),

    // An example of an Apple identifier.
    array(
      Specification::MEID . Format::hexadecimal => '35695706001456',
      Specification::MEID . Format::decimal => '089609600600005206',
      Specification::ESN . Format::hexadecimal => '80AAD01F',
      Specification::ESN . Format::decimal => '12811194399'
    )
  );


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
}
