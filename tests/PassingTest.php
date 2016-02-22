<?php

use RaphaelTraviss\CellularIdentifier\CellularIdentifier;

class PassingTest extends PHPUnit_Framework_TestCase {
  public function testThatPasses() {
    $identifier = new CellularIdentifier();
    return $identifier->doSomething();
  }
}
