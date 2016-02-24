<?php
/**
 * Containes the CellularIdentifier class.
 */

namespace Skyleaf\CellularIdentifier;

/**
 * Converts between various formats and specifications of cellular devices.
 *
 * This class performs the following general operations:
 *  - It matches the input identifier against patterns to determine the starting spec/format.
 *  - It responds to mutation requests by matching the current spec/format to a
 *    function that will perform the desired mutation.  All of these functions are referenced
 *    by a common set of strings contained in abstract class contants.
 *  - It performs the mutation and caches the new spec/format value.
 *  - After the mutations are performed, you can use the public API to get the new
 *    data, such as the current value and check digit.
 *
 * @implements CellularIdentifierInterface
 */
class CellularIdentifier implements CellularIdentifierInterface {
  public function hex() {
    $this->mutateToFormat(Format::hexadecimal);
    return $this;
  }

  public function dec() {
    $this->mutateToFormat(Format::decimal);
    return $this;
  }

  public function esn() {
    $this->mutateToSpecification(Specification::ESN);
    return $this;
  }

  public function meid() {
    $this->mutateToSpecification(Specification::MEID);
    return $this;
  }

  public function imei() {
    $this->mutateToSpecification(Specification::IMEI);
    return $this;
  }

  public function iccid() {
    $this->mutateToSpecification(Specification::ICCID);
    return $this;
  }

  public function value() {
    return $this->cachedValues[$this->specification . $this->format];
  }

  public function specification() {
    return $this->specification;
  }

  public function format() {
    return $this->format;
  }

  public function manufacturer() {
    // Not implemented yet.
    return null;
  }

  public function checkDigit() {

    // Calculating the check digit for an all-decimal hex MEID could fail, because you
    // can't be sure whether the device ID is a base-10 IMEI or a base-16 MEID.
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

  /**
   * Maps transformation functions to keys based on the current specification and format.
   *
   * Keys are of the form Specification . Format (original) . Format (desired).
   *
   * @var array
   */
  protected $formatTransformations = array();

  /**
   * Maps transformation functions to keys based on the current specification.
   *
   * Keys are of the form Specification (original) . Specification (desired).
   *
   * @var array
   */
  protected $specificationTransformations = array();






  // Magic methods.






  public function __construct($inputIdentifier) {
    // Initialize cached values as null.
    $cache_keys = array(
      Specification::ESN . Format::decimal,
      Specification::ESN . Format::hexadecimal,
      Specification::MEID . Format::decimal,
      Specification::MEID . Format::hexadecimal
    );
    $this->cachedValues = array_combine($cache_keys, array_fill(0, count($cache_keys), null));

    // Initialize format transformation function mapping; only ESN/MEID can be converted between formats.
    $formatter_function_keys = array(
      Specification::ESN . Format::hexadecimal . Format::decimal,
      Specification::ESN . Format::decimal . Format::hexadecimal,
      Specification::MEID . Format::hexadecimal . Format::decimal,
      Specification::MEID . Format::decimal . Format::hexadecimal,
    );

    $formatter_function = function($source_value, $source_radix, $destination_radix, $break_at_index, $prefix_length, $suffix_length) {
      $zeroPad = function($input, $length) use (&$zeroPad) {
        return ($length <= strlen($input)) ? $input : $zeroPad(0 . $input, $length);
      };

      // Break the input into two parts; transform, pad, and concatenate both parts.
      return strtoupper(
        $zeroPad(base_convert(substr($source_value, 0, $break_at_index), $source_radix, $destination_radix), $prefix_length) .
        $zeroPad(base_convert(substr($source_value, $break_at_index), $source_radix, $destination_radix), $suffix_length)
      );
    };

    // Fix for PHP 5.3 concatenated array keys.  Note that closures in PHP 5.3 can
    // only use the public API of an object passed into them.
    $php53FixThis = $this;

    $formatter_functions = array(
      // ESN hex to dec.
      function($valueToConvert) use ($formatter_function) {
        return $formatter_function($valueToConvert, 16, 10, 2, 3, 8);
      },
      // ESN dec to hex.
      function($valueToConvert) use ($formatter_function) {
        return $formatter_function($valueToConvert, 10, 16, 3, 2, 6);
      },
      // MEID hex to dec.
      function($valueToConvert) use ($formatter_function) {
        return $formatter_function($valueToConvert, 16, 10, 8, 10, 8);
      },
      //MEID dec to hex.
      function($valueToConvert) use ($formatter_function) {
        return $formatter_function($valueToConvert, 10, 16, 10, 8, 6);
      }
    );
    $this->formatTransformations = array_combine($formatter_function_keys, $formatter_functions);

    // Initialize specification transformation functions; currently there is only one.
    $specification_function_keys = array(
      Specification::MEID . Specification::ESN
    );

    $php53FixProtectedProperty = $this->formatTransformations;
    $specification_functions = array(
      // MEID -> ESN conversion function.
      function() use ($php53FixThis, $php53FixProtectedProperty) {
        // Given a hex identifier, returns a pseudo ESN.
        $calculatePseudoESN = function($meid_hex) {
          $output = '';
          for ($i = 0; $i < strlen($meid_hex); $i += 2){
            $output .= chr(intval(substr($meid_hex, $i, 2), 16));
          }
          $hash = sha1($output);
          return strtoupper("80".substr($hash,(strlen($hash) -6)));
        };

        // Conversions to pseudo ESN must be done using the hexadecimal MEID.
        $result = '';
        if ($php53FixThis->format() != Format::hexadecimal) {
          // Find the fuction to convert our current value into hex.
          $hex_function = $php53FixProtectedProperty[$php53FixThis->specification() . $php53FixThis->format() . Format::hexadecimal];
          // Get the hex value using the tranformation function we just found.
          $hex_meid = $hex_function($php53FixThis->value());
          // Calculate the pseudo ESN using the hex value we just found.
          $pseudo_ESN = $calculatePseudoESN($hex_meid);
          // Find yet another function to turn that pseudo ESN into our current format.
          $esn_function = $php53FixProtectedProperty[Specification::ESN . Format::hexadecimal . $php53FixThis->format()];
          // Transform the pseudo ESN into our current format.
          $result = $esn_function($pseudo_ESN);
        } else {
          $result = $calculatePseudoESN($php53FixThis->value());
        }

        return $result;
      }
    );

    $this->specificationTransformations = array_combine($specification_function_keys, $specification_functions);

    return $this->filterInput($inputIdentifier);
  }

  public function __toString() {
    return $this->value();
  }









  // Private utility functions.








  /**
   * Filter input text through patterns, and set internal state based on the results.
   *
   * @var: string $input The input text to process.
   *
   * @return: boolean true if the input could be filtered, false if it is invalid.
   */
  protected function filterInput($input) {
    // An IMEI with check digit is 15 characters long, all of them decimal.
    // IMEI are handled internally the same as hex MEID.  An identifier that matches
    // the pattern for an IMEI could actually be an MEID.
    if(preg_match('/^[0-9]{15}$/', $input)){
      $this->specification = Specification::IMEI;
      $this->format = Format::hexadecimal;

    // An IMEI without a check digit is 14 characters long, all of them decimal.
    } else if(preg_match('/^[0-9]{14}$/', $input)){
      $this->specification = Specification::IMEI;
      $this->format = Format::hexadecimal;

    // An MEID in hexadecimal format is 14 characters long, all of them decimals or letters a-f in any case.
    } else if(preg_match('/^[a-fA-F0-9]{14}$/', $input)){
      $this->specification = Specification::MEID;
      $this->format = Format::hexadecimal;

    // An MEID in decimal format is 18 characters long, all of them decimals.
    } else if(preg_match('/^[0-9]{18}$/', $input)){
      $this->specification = Specification::MEID;
      $this->format = Format::decimal;

    // An ESN in hexadecimal format is 8 characters long, all of them decimals or letters a-f in any case.
    } else if(preg_match('/^[a-fA-F0-9]{8}$/', $input)){
      $this->specification = Specification::ESN;
      $this->format = Format::hexadecimal;

    // An ESN in decimal format is 11 characters long, all of them decimals.
    } else if(preg_match('/^[0-9]{11}$/', $input)){
      $this->specification = Specification::ESN;
      $this->format = Format::decimal;

    // If none of the patterns matched, then they gave us something other than a device ID.
    } else {
      return false;
    }

    // Cache the input value, after making it uppercase.
    $this->cachedValues[$this->specification . $this->format] = strtoupper($input);
    return true;
  }

  /**
   * Mutates the identifier to a given format and caches values.
   *
   * @var: string $format The format to mutate to; constant of class Format.
   */
  protected function mutateToFormat($format) {
    if (isset($this->cachedValues[$this->specification . $format])) {
      $this->format = $format;
    }
    else if (isset($this->formatTransformations[$this->specification . $this->format . $format])) {
      $conversion_function = $this->formatTransformations[$this->specification . $this->format . $format];
      $conversion_result = $conversion_function($this->value());
      if ($conversion_result) {
        $this->cachedValues[$this->specification . $format] = $conversion_result;
        $this->format = $format;
      }
    }
  }

  /**
   * Mutates the identifier to a given specification and caches values.
   *
   * @var: string $specification The specification to mutate to; constant of class Specification.
   */
  protected function mutateToSpecification($specification) {
    if (isset($this->cachedValues[$specification . $this->format])) {
      $this->specification = $specification;
    }
    else if (isset($this->specificationTransformations[$this->specification . $specification])) {
      $conversion_function = $this->specificationTransformations[$this->specification . $specification];
      $conversion_result = $conversion_function();
      if ($conversion_result) {
        $this->cachedValues[$specification . $this->format] = $conversion_result;
        $this->specification = $specification;
      }
    }
  }
}
