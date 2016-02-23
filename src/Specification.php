<?php
/**
 * Contains the Specification abstract class.
 */

namespace Skyleaf\CellularIdentifier;

/**
 * Holds possible choices for specifications, similar to an enum in other languages.
 */
abstract class Specification {
  const ESN = 'esn';
  const MEID = 'meid';
  const IMEI = 'meid';
  const ICCID = 'iccid';
}
