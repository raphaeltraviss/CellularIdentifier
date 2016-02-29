[![Build Status](https://travis-ci.org/raphaeltraviss/CellularIdentifier.svg?branch=1.x)](https://travis-ci.org/raphaeltraviss/CellularIdentifier)

# Why does this exist?

Device identifiers from cellular equipment can have many different formats and follow multiple specifications.  Therefore, the same device can be referenced by many different device identifiers, which is a problem, because cellular serial numbers are often used as unique identifiers.

This class will allow you to convert a cellular device identification number from one format to another (hex to dec and vice-versa), and from one specification to another (e.g. from MEID to pseudo ESN).

If any additional mobile identifier specifications and formats appear in the future, this class can easily be amended to include them.




# How do I use this?

#### Checking if a given identifier is valid:
```php
  $identifier = new CellularIdentifier('A1000001FFFFFF');

  if($identifier) {
    // Iterate over identifier results.
  } else {
    // Do error handling.
  }
```

#### Converting between formats; returning a single value:
```php
  $identifier = new CellularIdentifier('123456789012345678');
  if ($identifier) {
    $identifier->specification(); // 'meid'
    $identifier->format(); // '10'

    $pseudo_esn = $identifier->hex()->esn()->value(); // '80FFFFFF'
    $identifier->specification(); // 'esn'
    $identifier->format(); // '16'

    // You could have also done $identifier->esn()->hex()->value().
  }

```

#### Converting between many formats; returning many values:
```php
  $identifier = new CellularIdentifier('99990000000000');

  if ($identifier) {
    // Do as many conversions as you want.
    $identifier->hex()->dec()->esn()->hex()->dec();

    // Only the valid spec/format values for the given identifier will be non-null.
    foreach ($identifier as $specification_and_format => $value) {
      if (isset($value)) {
        print $specification_and_format . $value . "\n";
      }
    }
  }
```

#### Calculating a check digit:
```php
  $identifier = new CellularIdentifier('99990000000001');

  if ($identifier) {
    $check_digit = $identifier->checkDigit();
  }
```


# How does this class work?

1. A regex determines the specification and format of the given identifier.
2. Values from the given identifier are cached.
3. Transformation methods are called on the identifier object.
4. A transformation function is retrieved from an array
5. The transformation function is applied to the identfier object.
6. The returned value of the transformation function is cached.
7. The internal state of the object changes to reflect the most recent transformation.

Since *so many* strings are being used to reference transformation functions and cached values, you will notice liberal use of `AbstractClass::someconstant`.  This is to reduce runtime errors for mis-typing strings, and also serves as a conceptual model which says, "These are specifications and formats, not just strings of characters."  Using abstract class constants is the simplest way to mimic an `enum` in PHP.

Also, you will notice we had to take liberties to make this class work in PHP 5.3, which has some minor array key and closure deficiencies.



# Limitations

#### All-decimal MEID inputs
There is some confusion between MEID and IMEI.  Some devices, such as Motorola world phones, have two separate radios; in which case, the IMEI and MEID will have nothing to do with each other.  Some devices, such as the Apple iPhone, use the IMEI as an MEID.  For calculation purposes, IMEI are base-10, and  MEID are base-16; which means that the check digit calculation for an all-decimal IMEI (base-10) and an all-decimal MEID (base-16) will be different, even though they contain the same decimal digits.  Therefore, if you instantiate a CellularIdentifier object with an all-decimal pattern that could be either an IMEI or an MEID, we assume that the pattern in an IMEI.

#### Check digit calculations
Only check digit calculations for decimal IMEIs are supported.
