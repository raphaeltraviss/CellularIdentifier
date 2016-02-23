<?php
/**
 * Containes the CellularIdentifierInterface interface.
 */

namespace Skyleaf\CellularIdentifier;

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
   * Do all transformations that are valid for the current identifier.
   *
   * @return: array
   */
  public function all();

  /**
   * Returns the value of the check digit for the current specification and format.
   *
   * The check digit is never included in $this->value(); you need to specifically
   * ask for it using this method, and append it yourself.
   *
   * @return: int
   */
  public function checkDigit();

  /**
   * Access the value of the current cellular identifier transformation.
   *
   * @return: string
   */
  public function value();

  /**
   * Access the format of the current cellular identifier tranformation.
   *
   * @return: string
   */
  public function format();

  /**
   * Access the specification of the current cellular identifier tranformation.
   *
   * @return: string
   */
  public function specification();
}
