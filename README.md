php-gettext
===========
This project is an implementation of [gettext](https://www.gnu.org/software/gettext/) in [PHP](https://secure.php.net/) 5.5.

Basic usage
-----------
Before starting, make sure every file is properly included, preferably using an auto loader.

1. Construct a new MO object. The constructor takes a string argument containing a .mo file `file_get_contents` can be used to supply such a file.

  ```php
$mo = new \gettext\MO(file_get_contents('my_file.mo'));
```
2. Translate a string. This is equivalent to PHP's [`gettext`](https://secure.php.net/manual/en/function.gettext.php) function.

  ```php
$translatedString = $mo->translate('My string.');
```
3. Translate a plural string. Equivalent to PHP's [`ngettext`](https://secure.php.net/manual/en/function.ngettext.php) function. (Note that the `translate` method does not automatically substitute %d. Use something like [`sprintf`](https://secure.php.net/manual/en/function.sprintf.php).)

  ```php
$numberOfStrings = 2;
$pluralString = $mo->translate('One string.', '%d strings.', $numberOfStrings);
```

Domains
-------
The `translate` method accepts a fourth argument to set the translation's domain.

The equivalent to [`dgettext`](https://secure.php.net/manual/en/function.dgettext.php) is therefore:
```php
$domain = 'my_domain';
$translatedString = $mo->translate('My string.', null, null, $domain);
```
While the equivalent to [`dngettext`](https://secure.php.net/manual/en/function.dngettext.php) is:
```php
$pluralString = $mo->translate('One string.', '%d strings.', $numberOfStrings, $domain);
```

Multiple files
--------------
It's possible to merge MO objects to support spreading translations over multiple files. To merge two MOs simply call the `merge` method, this will merge the second MO into the first.
```php
$mo1 = new \gettext\MO(file_get_contents('my_file_1.mo'));
$mo2 = new \gettext\MO(file_get_contents('my_file_2.mo'));
$mo1->merge($mo2);
```
