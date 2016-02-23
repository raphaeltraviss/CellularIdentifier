<?php
/**
 * Containes teh CellularIdentifier class.
 */

namespace Skyleaf\CellularIdentifier;

/**
 * Converts between various formats and specifications of cellular devices.
 *
 * @implements CellularIdentifierInterface
 */
class CellularIdentifier implements CellularIdentifierInterface {
  public function hex() {
    switch ($this->specification) {
      case Specification::ESN:
        if (!isset($this->cachedValues[Specification::ESN . Format::hexadecimal])) {
          $this->cachedValues[Specification::ESN . Format::hexadecimal] = $this->transformIdentifier(16, 3, 2, 6);
        }
        $this->format = Format::hexadecimal;
        break;
      case Specification::IMEI:
        // Do nothing.  The IMEI specification calls for all-decimal digits.
        break;
      case Specification::MEID:
        if (!isset($this->cachedValues[Specification::MEID . Format::hexadecimal])) {
          $this->cachedValues[Specification::MEID . Format::hexadecimal] = $this->transformIdentifier(16, 10, 8, 6);
        }
        $this->format = Format::hexadecimal;
        break;
      case Specification::ICCID:
        // Not implemented yet.
        break;
    }

    return $this;
  }

  public function dec() {

    $this->format = Format::decimal;
    return $this;
  }

  public function esn() {

    $this->specification = Specification::ESN;
    return $this;
  }

  public function meid() {

    $this->specification = Specification::MEID;
    return $this;
  }

  public function imei() {

    $this->specification = Specification::IMEI;
    return $this;
  }

  public function iccid() {
    // Not implemented
    $this->specification = Specification::ICCID;
    return $this;
  }

  public function value() {
    print_r($this->cachedValues);
    return $this->cachedValues[$this->specification . $this->format];
  }

  public function getFormat() {
    return '';
  }

  public function getSpecification() {
    return '';
  }

  public function checkDigit() {
    $check_digit = false;

    // Check digits must always be calculated using the
    if (!isset($this->cachedValues[Specification::MEID . Format::hexadecimal])) {

    }

    $radix = $this->radix[$this->format];

    $digits = array_reverse(str_split($this->value));
    $digit_sum = function($checkstring) {
      return substr((string) $radix - (array_sum(str_split($checkstring)) % $radix), -1, 1);
    };

    $checkstring = '';
    switch ($radix) {
      case 10:
        foreach ($digits as $i => $d) {
          $checkstring .= $i %2 !== 0 ? $d * 2 : $d;
        }
        $check_digit = $digit_sum($checkstring);
        break;
      case 16:
        foreach ($digits as $i => $d) {
          // Convert to dec so PHP can do math.
          $d = hexdec($d);
          $checkstring .= $i %2 !== 0 ? $d * 2 : $d;
        }
        $check_digit = dechex($digit_sum($checkstring));
        break;
    }

    return $check_digit;
  }

  /**
   * Dummy method for Travis and PHPUnit.
   */
  public function doSomething() {
    return true;
  }







  // Internal state







  /**
   * The format of the current identifier.
   *
   * @var string One of Format::[format]
   */
  protected $format;

  /**
   * The specification of the current identifier.
   *
   * @var string One of Specification::[specification]
   */
  protected $specification;

  /**
   * An array which holds already-calculated values for the device identifier.
   *
   * @var array
   */
  protected $cachedValues = array();






  // Magic methods.






  public function __construct($inputIdentifier) {
    // Inintialize cached values all as null.
    $cacheKeys = array(
      Specification::ESN . Format::decimal,
      Specification::ESN . Format::hexadecimal,
      Specification::MEID . Format::decimal,
      Specification::MEID . Format::hexadecimal,
      Specification::IMEI . Format::checkDigit,
      Specification::ICCID . Format::decimal
    );
    $this->cachedValues = array_combine($cacheKeys, array_fill(0, count($cacheKeys), null));

    return $this->filterInput($inputIdentifier);
  }

  public function __toString() {
    return $this->value();
  }






  // Private utility functions.







  /**
   * Maps a format to an integer radix.
   *
   * @var array
   */
  protected $radix = array(
    Format::decimal => 10,
    Format::hexadecimal => 16
  );


  /**
   * Filter input text through patterns, and set internal state based on the results.
   *
   * @var: string $input The input text to process.
   *
   * @return: boolean true if the input could be filtered, false if it is invalid.
   */
  protected function filterInput($input) {
    // An IMEI with check digit is 15 characters long, all of them decimal.
    if(preg_match('/^[0-9]{15}$/', $input)){
      $this->cachedValues[Specification::IMEI . Format::decimal] = substr($input, 0, -1);
      $this->cachedValues[Specification::IMEI . Format::checkDigit] = substr($input, 13, 1);
      $this->format = Format::decimal;
      $this->specification = Specification::IMEI;

    // An IMEI without a check digit is 14 characters long, all of them decimal.
    } else if(preg_match('/^[0-9]{14}$/', $input)){
      $this->cachedValues[[Specification::IMEI . Format::decimal]] = $input;
      $this->format = Format::decimal;
      $this->specification = Specification::IMEI;

    // An MEID in hexadecimal format is 14 characters long, all of them decimals or letters a-f in any case.
    } else if(preg_match('/^[a-fA-F0-9]{14}$/', $input)){
      $this->cachedValues[Specification::MEID . Format::hexadecimal] = $input;
      $this->format = Format::hexadecimal;
      $this->specification = Specification::MEID;

    // An MEID in decimal format is 18 characters long, all of them decimals.
    } else if(preg_match('/^[0-9]{18}$/', $input)){
      $this->cachedValues[Specification::MEID . Format::decimal] = $input;
      $this->format = Format::decimal;
      $this->specification = Specification::MEID;

    // An ESN in hexadecimal format is 8 characters long, all of them decimals or letters a-f in any case.
    } else if(preg_match('/^[a-fA-F0-9]{8}$/', $input)){
      $this->cachedValues[Specification::ESN . Format::hexadecimal] = $input;
      $this->format = Format::hexadecimal;
      $this->specification = Specification::ESN;

    // An ESN in decimal format is 11 characters long, all of them decimals.
    } else if(preg_match('/^[0-9]{11}$/', $input)){
      $this->cachedValues[Specification::ESN . Format::decimal] = $input;
      $this->format = Format::decimal;
      $this->specification = Specification::ESN;

    // If none of the patterns matched, then they gave us something other than a device ID.
    } else {
      return false;
    }

    $this->value = $input;
    return true;
  }

  /**
   * Calculates a hexadecimal pseudo ESN, given a hexadecimal MEID without a check digit.
   *
   * @return  string The calculated pseudo ESN.
   */
  protected function calculatePseudoESN($identifier){
    $p = '';
    for ($i = 0; $i < strlen($input); $i += 2){
      $p .= chr(intval(substr($input, $i, 2), 16));
    }
    $hash = sha1($p);

    return strtoupper("80".substr($hash,(strlen($hash) -6)));
  }

  /**
   * Transforms a serial number from hexadecimal to decimal, or vise versa.
   *
   * This is done by splitting the identifier into two parts, converting each part,
   * and then padding with zeroes until the desired lenght is reached to fit the specification.
   *
   * @param int $destinationRadix - The radix of the transformed serial number.
   * @param int $breakAtCharacterIndex - The length of the first part of the destination identifier format/specification.
   * @param int $prefixLength - The length of the first part of the destination identifier after transformation.
   * @param int $suffixLength - The length of the second part of the destination identifier after transformation.
   * @return string - The transformed serial number
   */
    protected function transformIdentifier($destinationRadix, $breakAtCharacterIndex, $prefixLength, $suffixLength) {
      $zeroPad = function($input, $length) use (&$zeroPad) {
        if ($length <= strlen($input)) {
          return $input;
        }
        return $zeroPad(0 . $input, $length);
      };

      // Break the input into two parts.  For each part, transform the the destination radix,
      // and left-pad with zeroes until it meets the spec.
      $currentRadix = $this->radix[$this->format];
      $result = strtoupper(
        $zeroPad(base_convert(substr($this->value, 0, $breakAtCharacterIndex), $currentRadix, $destinationRadix), $prefixLength) .
        $zeroPad(base_convert(substr($this->value, $breakAtCharacterIndex), $currentRadix, $destinationRadix), $suffixLength)
      );

      return $result;
    }
}
