<?php

use Skyleaf\CellularIdentifier\CellularIdentifier;
use Skyleaf\CellularIdentifier\Specification;
use Skyleaf\CellularIdentifier\Format;

class ConversionTest extends PHPUnit_Framework_TestCase {

  /**
   * Test format conversion when given a random specification/format.
   *
   * Pick three random devices; for each one, select a random specification to
   * initialize the identifier.  Convert to hex and dec; check that value, specification,
   * and format methods return the expected values.
   */
  public function testFormatMutation() {
    for ($i = 0; $i <= 2; $i++) {
      $known_device = array_rand($this->exampleDevices);
      $known_identifier = array_rand($this->exampleDevices[$known_device]);
      $random_identifier = $this->exampleDevices[$known_device][$known_identifier];

      $identifier = new CellularIdentifier($random_identifier);
      $specification = $identifier->specification();

      $hex_value = $identifier->hex()->value();
      $this->assertEquals(strtoupper($this->exampleDevices[$known_device][$specification . Format::hexadecimal]), $hex_value);
      $this->assertEquals($identifier->specification(), $specification);
      $this->assertEquals($identifier->format(), Format::hexadecimal);


      $dec_value = $identifier->dec()->value();
      $this->assertEquals($this->exampleDevices[$known_device][$specification . Format::decimal], $identifier->value());
      $this->assertEquals($identifier->specification(), $specification);
      $this->assertEquals($identifier->format(), Format::decimal);
    }
  }

  /**
   * Test format conversion when given a random specification/format.
   *
   * Pick three random devices; for each one, select a random specification to
   * initialize the identifier.  Convert to hex and dec; check that value, specification,
   * and format methods return the expected values.
   */
  public function testSpecificationMutation() {
    for ($i = 0; $i <= 2; $i++) {
      $known_device = array_rand($this->exampleDevices);
      $known_identifier = array_rand($this->exampleDevices[$known_device]);
      $random_identifier = $this->exampleDevices[$known_device][$known_identifier];

      $identifier = new CellularIdentifier($random_identifier);
      $format = $identifier->format();

      // You can only convert specifications from MEID to ESN.
      // Test mutating to every format, even those that have no implementation,
      // and then finally mutate to ESN.
      $esn_value = $identifier->meid()->imei()->iccid()->esn()->value();

      $php53_array_key = Specification::ESN . $format;

      $this->assertEquals(strtoupper($this->exampleDevices[$known_device][$php53_array_key]), $esn_value);
      $this->assertEquals($identifier->specification(), Specification::ESN);
      $this->assertEquals($identifier->format(), $format);
    }
  }

  /**
   * Test format conversion when given a random specification/format.
   *
   * Pick three random devices; for each one, select a random specification to
   * initialize the identifier.  Convert to hex and dec; check that value, specification,
   * and format methods return the expected values.
   */
  public function testMEIDToESNMutation() {
    for ($i = 0; $i <= 4; $i++) {
      $known_device = array_rand($this->exampleDevices);
      $known_identifier = array_rand($this->exampleDevices[$known_device]);
      $random_identifier = $this->exampleDevices[$known_device][$known_identifier];

      $identifier = new CellularIdentifier($random_identifier);
      $specification = $identifier->specification();
      $format = $identifier->format();

      // This test is only concerned with the MEID->ESN special case.
      if ($specification == Specification::MEID) {
          // Re-form $identifier->cachedValues.
        $value_keys = array(
          Specification::MEID . Format::hexadecimal,
          Specification::MEID . Format::decimal,
          Specification::ESN . Format::hexadecimal,
          Specification::ESN . Format::decimal,
        );

        // Perform all mutations.
        // We intentionally mutate to a decimal format before the ESN conversion,
        // to make sure that the library can handle this.
        $identifier_values = array();
        $identifier_values[] = $identifier->hex()->value();
        $identifier_values[] = $identifier->dec()->value();
        $identifier_values[] = $identifier->esn()->hex()->value();
        $identifier_values[] = $identifier->dec()->value();


        $values = array_combine($value_keys, $identifier_values);

        // Check to make sure that our values match the pre-computed ones we expect.
        foreach ($values as $key => $value) {
          $this->assertEquals(strtoupper($this->exampleDevices[$known_device][$key]), $values[$key]);
        }
      }
    }
  }


  // IMEI should not convert to MEID decimal.

  // IMEI->hex() should not change the format.

  // ESN->meid should not change the specification.

  // IMEI->MEID or ESN should not change the specification.






  // Private internal state.






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
