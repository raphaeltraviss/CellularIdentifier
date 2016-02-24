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
   * No other formats besides MEID can be converted to MEID.
   * IMEIs are valid MEIDs, but not for purposes of calculation, since the radix is different.
   *
   * @return: CellularIdentifier
   */
  public function meid();

  /**
   * Transforms a device identifier to conform to an IMEI specification.
   *
   * No other formats besides IMEI can be converted to IMEI.
   * Be aware that some devices have TWO identifiers.  This would be true for
   * phones that have both a CDMA and a GSM radio; they may have one unique identifier
   * for each.  In which case, the two IMEI/MEIDs on the label will not match.  Other
   * devices, such as the Apple iPhone, use the same identifier for both, in which
   * case, a valid IMEI is also a valid MEID.
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
