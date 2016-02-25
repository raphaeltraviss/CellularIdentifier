<?php

use Skyleaf\CellularIdentifier\CellularIdentifier;
use Skyleaf\CellularIdentifier\Specification;
use Skyleaf\CellularIdentifier\Format;

class CellularIdentifierTest extends PHPUnit_Framework_TestCase {

  /**
   * Test format conversion when given a random specification/format.
   *
   * Pick three random devices; for each one, select a random specification to
   * initialize the identifier.  Convert to hex and dec; check that value, specification,
   * and format methods return the expected values.
   */
  public function testFormatMutation() {
    for ($i = 0; $i <= 10; $i++) {
      $device_index = array_rand($this->exampleDevices);
      $identifier_key = array_rand($this->exampleDevices[$device_index]);
      $random_identifier = $this->exampleDevices[$device_index][$identifier_key];

      $identifier = new CellularIdentifier($random_identifier);
      $specification = $identifier->specification();

      // Only ESN and MEID have hex/dec equivalence.
      if ($specification == Specification::ESN || $specification == Specification::MEID) {
        $hex_value = $identifier->hex()->value();
        // Check that the calculated value matches the expected value.
        $this->assertEquals(strtoupper($this->exampleDevices[$device_index][$specification . $identifier->format()]), $hex_value);
        // Check that the specification didn't change.
        $this->assertEquals($identifier->specification(), $specification);
        // Check that the format changed to hexadecimal.
        $this->assertEquals($identifier->format(), Format::hexadecimal);

        // Change the format to decimal, and verifiy only the format changed.
        $dec_value = $identifier->dec()->value();
        $this->assertEquals($this->exampleDevices[$device_index][$specification . Format::decimal], $dec_value);
        $this->assertEquals($identifier->specification(), $specification);
        $this->assertEquals($identifier->format(), Format::decimal);
      }
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
    for ($i = 0; $i <= 10; $i++) {
      $device_index = array_rand($this->exampleDevices);
      $identifier_key = array_rand($this->exampleDevices[$device_index]);
      $random_identifier = $this->exampleDevices[$device_index][$identifier_key];

      $identifier = new CellularIdentifier($random_identifier);
      $format = $identifier->format();

      // You can only convert specifications from MEID to ESN.
      // Test mutating to every format, even those that have no implementation,
      // and then finally mutate to ESN.
      $esn_value = $identifier->meid()->imei()->iccid()->esn()->value();

      $php53_array_key = Specification::ESN . $format;

      $this->assertEquals(strtoupper($this->exampleDevices[$device_index][$php53_array_key]), $esn_value);
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
    for ($i = 0; $i <= 10; $i++) {
      $device_index = array_rand($this->exampleDevices);
      $identifier_key = array_rand($this->exampleDevices[$device_index]);
      $random_identifier = $this->exampleDevices[$device_index][$identifier_key];

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
        $identifier_values = array(
          $identifier->hex()->value(),
          $identifier->dec()->value(),
          $identifier->esn()->hex()->value(),
          $identifier->dec()->value()
        );
        $values = array_combine($value_keys, $identifier_values);

        // Check to make sure that our values match the pre-computed ones we expect.
        foreach ($values as $key => $value) {
          $this->assertEquals(strtoupper($this->exampleDevices[$device_index][$key]), $values[$key]);
        }
      }
    }
  }

  public function testIteration() {
    for ($i = 0; $i <= 10; $i++) {
      $device_index = array_rand($this->exampleDevices);
      $identifier_key = array_rand($this->exampleDevices[$device_index]);
      $random_identifier = $this->exampleDevices[$device_index][$identifier_key];

      $identifier = new CellularIdentifier($random_identifier);
      $specification = $identifier->specification();

      // Fill the cached values with as many conversions as possible.
      $identifier->meid()->hex()->dec()->esn()->hex()->dec();

      // ESNs should generate a hex/dec.  MEID should generate two hex/dev pairs.
      $expected_count = 0;
      switch ($specification) {
        case Specification::IMEI:
          $expected_count = 5;
          break;
        case Specification::MEID:
          $expected_count = 4;
          break;
        case Specification::ESN:
          $expected_count = 2;
          break;
        default:
          $expected_count = 1;
          break;
      }

      $actual_count = 0;
      print "|  All values for " . $random_identifier . "\n";
      foreach ($identifier as $specification_and_format => $value) {
        if (isset($value)) {
          $actual_count++;
          print "|  " . $specification_and_format . " - " . $value . "\n";
        }
      }
      print "------------------------------\n";

      $this->assertEquals($expected_count, $actual_count);
    }
  }

  public function testCheckCalculation() {
    for ($i = 0; $i <= 10; $i++) {
      $device_index = array_rand($this->exampleDevices);
      $identifier_key = array_rand($this->exampleDevices[$device_index]);
      $random_identifier = $this->exampleDevices[$device_index][$identifier_key];

      $identifier = new CellularIdentifier($random_identifier);
      $specification = $identifier->specification();
      $format = $identifier->format();

      // Only attempt to calculate a check digit for hexadecimal MEID.
      if ($specification == Specification::MEID || $specification == Specification::IMEI) {
        $expected_check_digit = $this->exampleCheckDigits[$device_index][$identifier_key];
        if ($expected_check_digit) {
          $this->assertEquals($expected_check_digit, $identifier->checkDigit());
        }
      } else {
        $this->assertFalse($identifier->checkDigit());
      }
    }
  }








  // Private internal state.






  /**
   * A list of imaginary devices, with pre-calculated values.
   *
   * In order to accurately test this library against actual device identifiers, these
   * made-up values are inside the valid manufacturer prefix and serial number ranges,
   * meaning that they could intersect with an actual device, somewhere: if this
   * occurs, it is purely coincidental.  The MEID specification does not define any
   * ranges for testing and examples. Filled during initialization.
   */
  protected $exampleDevices = array();

  /**
   * A list of check digits to match the formats of the example devices.
   */
  protected $exampleCheckDigits = array();








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
      '35695706001456', // Test IMEI also valid as an MEID.
      '089609600600005206',
      '80Aad01F',
      '12811194399'
    );
    $apple_device = array_combine($device_array_keys, $apple_values);

    $this->exampleDevices = array($starcom_device, $samsung_device, $lg_device, $apple_device);

    $checkDigitValues = array(
      array('6', false), // starcom check digits.
      array(false, false), // samsung check digits.
      array(false, false), // LG check digits.
      array('1', false) // Apple check digits.
    );
    foreach($checkDigitValues as $value) {
      $this->exampleCheckDigits[] = array_combine(array(
        Specification::MEID . Format::hexadecimal,
        Specification::MEID . Format::decimal), $value);
    }
  }
}
