<?php
/**
 * Contains the Specification abstract class.
 */

namespace RaphaelTraviss\CellularIdentifier;

/**
 * Holds possible choices for specifications, similar to an enum in other languages.
 */
abstract class Specification {
  const esn = 'ESN';
  const meid = 'MEID';
  const imei = 'IMEI';
  const iccid = 'ICCID';
}
