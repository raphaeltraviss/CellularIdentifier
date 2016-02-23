<?php

use Skyleaf\CellularIdentifier\CellularIdentifier;

class ConversionTest extends PHPUnit_Framework_TestCase {
  public function testThatPasses() {
    $identifier = new CellularIdentifier('089609600609652614');
    $identifier->hex();
    echo('something');
    echo($identifier->value());
    return true;
  }

  // IMEI should not convert to MEID decimal.

  // IMEI->hex() should not change the format.
}
