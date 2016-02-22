<?php
/**
 * Containes the CellularIdentifierInterface interface.
 */

namespace RaphaelTraviss\CellularIdentifier;

/**
 * Specifies the public API that Cellular identifier classes must conform to.
 */
interface CellularIdentifierInterface {

  // Transformation methods

  /**
   * Transforms a device identifier into a hex format.
   *
   * @return: CellularIdentifier
   */
  public function hex();

  /**
   * Transforms a device identifier into a hex format.
   *
   * @return: CellularIdentifier
   */
  public function dec();

  /**
   * Transforms a device identifier to conform to an ESN specification.
   *
   * @return: CellularIdentifier
   */
  public function esn();

  /**
   * Transforms a device identifier to conform to an MEID specification.
   *
   * @return: CellularIdentifier
   */
  public function meid();

  /**
   * Transforms a device identifier to conform to an IMEI specification.
   *
   * @return: CellularIdentifier
   */
  public function imei();

  /**
   * Transforms a device identifier to conform to an ICCID specification.
   *
   * @return: CellularIdentifier
   */
  public function iccid();

  /**
   * Transforms a device identifier to include the check digit.
   *
   * The check digit is never included in $this->value() unless you specifically
   * ask for it using this method.
   *
   * @return: CellularIdentifier
   */
  public function withCheckDigit();

  /**
   * Access the value of the current cellular identifier transformation.
   *
   * @return: string
   */
  public function getValue();

  /**
   * Access the format of the current cellular identifier tranformation.
   *
   * @return: string
   */
  public function getFormat();

  /**
   * Access the specification of the current cellular identifier tranformation.
   *
   * @return: string
   */
  public function getSpecification();

  /**
   * Access the current value of the check digit for the current cellular identifier transformation.
   *
   * @return: string
   */
  public function getCheckDigit();
}
