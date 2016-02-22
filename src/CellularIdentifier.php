<?php
/**
 * Containes teh CellularIdentifier class.
 */

namespace RaphaelTraviss\CellularIdentifier;

/**
 * Converts between various formats and specifications of cellular devices.
 *
 * @implements CellularIdentifierInterface
 */
class CellularIdentifier implements CellularIdentifierInterface {
  /**
   * Dummy method for Travis and PHPUnit.
   */
  public function doSomething() {
    return true;
  }




  // Internal state




  /**
   * The value of the current identifier.
   *
   * @var string
   */
  private $value;

  /**
   * The format of the current identifier.
   *
   * @var string One of Format::[format]
   */
  private $format;

  /**
   * The specification of the current identifier.
   *
   * @var string One of Specification::[specification]
   */
  private $specification;

  /**
   * The base of the number system of the current identifier.
   *
   * The base and the format can be different; for example, an IMEI can only be
   * in decimal, yet it is handled internally as if it were a hex MEID.
   *
   * @var int
   */
  private $base;

  /**
   * Indicates whether or not the current identifier hold a valid value.
   *
   * @var boolean
   */
  private $isValid;






  // Public API documented in CellularIdentifierInterface





  public function hex() {
    return $this;
  }

  public function dec() {
    return $this;
  }

  public function esn() {
    return $this;
  }

  public function meid() {
    return $this;
  }

  public function imei() {
    return $this;
  }

  public function iccid() {
    return $this;
  }

  public function withCheckDigit() {
    return $this;
  }

  public function getValue() {
    return '';
  }

  public function getFormat() {
    return '';
  }

  public function getSpecification() {
    return '';
  }

  public function getCheckDigit() {
    return '';
  }
}
