<?php
/**
 * Containes the CellularIdentifier class.
 */
namespace Skyleaf\CellularIdentifier;
use Iterator;

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
class CellularIdentifier implements CellularIdentifierInterface, Iterator {







  // CellularIdentifierInterface public API.







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
    // Check digits are only valid for IMEI and MEID formats.
    if ($this->specification == Specification::IMEI && $this->format == Format::decimal) {
      $digits = str_split((string) $this->value());
      $digits[14] = 0;
      $digits = array_reverse($digits);

      $digit_sum = function($checkstring) {
        return substr((string) 10 - (array_sum(str_split($checkstring)) % 10), -1, 1);
      };

      $checkstring = '';
      switch (intval($this->format())) {
        case 10:
          foreach ($digits as $i => $d) {
            $checkstring .= $i %2 !== 0 ? $d * 2 : $d;
          }
          return $digit_sum($checkstring);
          break;
        case 16:
          foreach ($digits as $i => $d) {
            // Convert to dec so PHP can do math.
            $d = hexdec($d);
            $checkstring .= $i %2 !== 0 ? $d * 2 : $d;
          }
          return dechex($digit_sum($checkstring));
          break;
        default:
          return false;
      }
      return $check_digit;
    }
    return false;
  }



  // Protected public API to support CellularIdentifierInterface.



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
    if (preg_match('/^[0-9]{20}$/', $input)) {
      $this->specification = Specification::ICCID;
      $this->format = Format::decimal;

    // An IMEI with check digit is 15 characters long, all of them decimal.
    // IMEI are handled internally the same as hex MEID.  An identifier that matches
    // the pattern for an IMEI could actually be an MEID.
    } else if (preg_match('/^[0-9]{15}$/', $input)) {
      $this->specification = Specification::IMEI;
      $this->format = Format::decimal;
      // Save the check digit and trim it off.
      $check_digit = substr($input, 14, 1);
      $input = substr($input, 0, 14);
      // @todo: trigger check digit validation.

    // An IMEI without a check digit is 14 characters long, all of them decimal.
    } else if (preg_match('/^[0-9]{14}$/', $input)) {
      $this->specification = Specification::IMEI;
      $this->format = Format::decimal;

    // An MEID in hexadecimal format is 14 characters long, all of them decimals or letters a-f in any case.
    } else if (preg_match('/^[a-fA-F0-9]{14}$/', $input)) {
      $this->specification = Specification::MEID;
      $this->format = Format::hexadecimal;

    // An MEID in decimal format is 18 characters long, all of them decimals.
    } else if (preg_match('/^[0-9]{18}$/', $input)) {
      $this->specification = Specification::MEID;
      $this->format = Format::decimal;

    // An ESN in hexadecimal format is 8 characters long, all of them decimals or letters a-f in any case.
    } else if (preg_match('/^[a-fA-F0-9]{8}$/', $input)) {
      $this->specification = Specification::ESN;
      $this->format = Format::hexadecimal;

    // An ESN in decimal format is 11 characters long, all of them decimals.
    } else if (preg_match('/^[0-9]{11}$/', $input)) {
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



  // Private internal state to support CellularIdentifierInterface.



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
   * An array which holds already-calculated values for the device identifier.
   *
   * @var array
   */
  private $cachedValues = array();







  // Iterator public API.  This class implement Iterator itself, because we need
  // to access private cachedValues.







  public function current() {
    $cached_value_keys = array_keys($this->cachedValues);
    return $this->cachedValues[$cached_value_keys[$this->currentIndex]];
  }

  public function key() {
    $cached_value_keys = array_keys($this->cachedValues);
    return $cached_value_keys[$this->currentIndex];
  }

  public function next() {
    $this->currentIndex++;
  }

  public function rewind() {
    $this->currentIndex = 0;
  }

  public function valid() {
    return $this->currentIndex <= (count($this->cachedValues) - 1);
  }



  // Private internal state to support Iterator.



  /**
   * An integer that represent our current position within the iteration of values.
   *
   * @var int
   */
  private $currentIndex = 0;








  // Magic methods.






  public function __construct($inputIdentifier) {
    // Initialize cached values as null; done here due to PHP 5.3 array key limitations.
    $cache_keys = array(
      Specification::ICCID . Format::decimal,
      Specification::IMEI . Format::decimal,
      Specification::MEID . Format::hexadecimal,
      Specification::MEID . Format::decimal,
      Specification::ESN . Format::hexadecimal,
      Specification::ESN . Format::decimal,
    );
    $this->cachedValues = array_combine($cache_keys, array_fill(0, count($cache_keys), null));

    // Initialize format transformation function mapping; only ESN/MEID can be converted between formats.
    $formatter_function_keys = array(
      Specification::ESN . Format::hexadecimal . Format::decimal,
      Specification::ESN . Format::decimal . Format::hexadecimal,
      Specification::MEID . Format::hexadecimal . Format::decimal,
      Specification::MEID . Format::decimal . Format::hexadecimal
    );

    // Given a hex identifier, returns a pseudo ESN.
    $calculatePseudoESN = function($meid_hex) {
      $output = '';
      for ($i = 0; $i < strlen($meid_hex); $i += 2){
        $output .= chr(intval(substr($meid_hex, $i, 2), 16));
      }
      $hash = sha1($output);
      return strtoupper("80".substr($hash,(strlen($hash) -6)));
    };

    // Convert a device identifier between base-10 and base-16.
    $convert_identifier = function($source_value, $source_radix, $destination_radix, $break_at_index, $prefix_length, $suffix_length) {
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
    $identifier_object = $this;

    $formatter_functions = array(
      // ESN hex to dec.
      function($value_to_convert) use ($convert_identifier) {
        return $convert_identifier($value_to_convert, 16, 10, 2, 3, 8);
      },
      // ESN dec to hex.
      function($value_to_convert) use ($convert_identifier) {
        return $convert_identifier($value_to_convert, 10, 16, 3, 2, 6);
      },
      // MEID hex to dec.
      function($value_to_convert) use ($convert_identifier) {
        return $convert_identifier($value_to_convert, 16, 10, 8, 10, 8);
      },
      //MEID dec to hex.
      function($value_to_convert) use ($convert_identifier) {
        return $convert_identifier($value_to_convert, 10, 16, 10, 8, 6);
      }
    );
    $this->formatTransformations = array_combine($formatter_function_keys, $formatter_functions);

    // Initialize specification transformation functions; currently there is only one.
    $specification_function_keys = array(
      Specification::MEID . Specification::ESN,
      Specification::IMEI . Specification::MEID,
      Specification::IMEI . Specification::ESN
    );

    $format_function_lookup = $this->formatTransformations;

    // Specification tranformations use the format transformations, and are declared afterwards.
    $specification_functions = array(

      // MEID -> ESN conversion function.
      function($value_to_convert) use ($identifier_object, $format_function_lookup, $calculatePseudoESN) {
        // Conversions to pseudo ESN must be done using the hexadecimal MEID.
        $result = '';
        if ($identifier_object->format() == Format::hexadecimal) {
          return $calculatePseudoESN($value_to_convert);
        } else {
          // Find the fuction to convert our current value into hex.
          $hex_function = $format_function_lookup[$identifier_object->specification() . $identifier_object->format() . Format::hexadecimal];
          // Get the hex value using the tranformation function we just found.
          $hex_meid = $hex_function($value_to_convert);
          // Calculate the pseudo ESN using the hex value we just found.
          $pseudo_ESN = $calculatePseudoESN($hex_meid);
          // Find yet another function to turn that pseudo ESN into our current format.
          $esn_function = $format_function_lookup[Specification::ESN . Format::hexadecimal . $identifier_object->format()];
          // Transform the pseudo ESN into our current format.
          return $esn_function($pseudo_ESN);
        }
      },

      // IMEI -> MEID conversion function.
      function($value_to_convert) use ($format_function_lookup) {
        // Pretend our decimal IMEI is a hexadecimal MEID, and then convert it to a
        // decimal MEID to preserve $this->format().  Confused yet?
        $dec_function = $format_function_lookup[Specification::MEID . Format::hexadecimal . Format::decimal];

        return $dec_function($value_to_convert);
      },

      // IMEI -> ESN conversion function.
      function($value_to_convert) use ($identifier_object, $format_function_lookup, $calculatePseudoESN) {
        // Using the IMEI value as a hex MEID, convert to a hex ESN.
        $pseudo_esn = $calculatePseudoESN($value_to_convert);
        // Look up the function that will convert a hex ESN to decimal.
        $esn_dec_function = $format_function_lookup[Specification::ESN . Format::hexadecimal . Format::decimal];
        // Return the converted decimal ESN.
        return $esn_dec_function($pseudo_esn);
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
   * Mutates the identifier to a given format and caches values.
   *
   * @var: string $format The format to mutate to; constant of class Format.
   */
  private function mutateToFormat($format) {
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
  private function mutateToSpecification($specification) {
    if (isset($this->cachedValues[$specification . $this->format])) {
      $this->specification = $specification;
    }
    else if (isset($this->specificationTransformations[$this->specification . $specification])) {
      $conversion_function = $this->specificationTransformations[$this->specification . $specification];
      $conversion_result = $conversion_function($this->value());
      if ($conversion_result) {
        $this->cachedValues[$specification . $this->format] = $conversion_result;
        $this->specification = $specification;
      }
    }
  }
}
