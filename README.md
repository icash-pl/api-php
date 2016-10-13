iCash.pl: API PHP
==

## Getting started

```php
<?php
$icash = new iCash('YOU_APP_KEY');
$icash->getStatusCode(array(
	'service' => '2',
	'number' => '7055',
	'code' => '9AB5KJ'
));

// ok
if ($icash->statusOk()) {

}
// error
else {

}
?>
```