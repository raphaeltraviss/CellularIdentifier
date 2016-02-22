# Why does this exist?

Device identifiers from cellular equipment can have many different formats and follow multiple specifications.  Therefore, the same device can be referenced by many different device identifiers, which is a problem, because cellular serial numbers are often used as unique identifiers.

This class will allow you to convert a cellular device identification number from one format to another (hex to dec and vice-versa), and from one specification to another (e.g. from MEID to pseudo ESN).

# How do I use this?
```php
// All identifiers and conversion values in this example are fictitious.
$identifier = new CellularIdentifier('123456789012345678');
$identifier->specification; // 'MEID'
$identifier->format; // 'decimal' 
$pseudo_esn = CellularIdentifier->hex()->esn()->value(); // '80a547e3'
$identifier->specification; // 'ESN'
$identifier->format; // 'hexadecimal'
$hex_identifer = CellularIdentifier->hex()->value(); // '499602D2BC614E'
```
Instantiate the CellularIdentifier object with a cellular device identifier in any format or specification.  Apply transformation methods such as `hex()` and `meid()` to change the internal state of the object.  As you do this, properties such as `specification` and `format` will change to match the current state.
