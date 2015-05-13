# Nether Input

[![nether.io](https://img.shields.io/badge/nether-input-C661D2.svg)](http://nether.io/) [![Code Climate](https://codeclimate.com/github/netherphp/input/badges/gpa.svg)](https://codeclimate.com/github/netherphp/input) [![Build Status](https://travis-ci.org/netherphp/input.svg)](https://travis-ci.org/netherphp/input)  [![Packagist](https://img.shields.io/packagist/v/netherphp/input.svg)](https://packagist.org/packages/netherphp/input) [![Packagist](https://img.shields.io/packagist/dt/netherphp/input.svg)](https://packagist.org/packages/netherphp/input)

An input filtering interface. It allows you to define a set of dynamic filters
for input to be run applied just-in-time on your specified data source.

Super simple example:

	$input = (new Nether\Input\Filter)
	->Email(function($t){ return filter_var($t,FILTER_VALIDATE_EMAIL); });

	// ... some time later...

	$input->SetDataset($_POST);
	if(!$input->Email) {
		throw new Exception('valid email address is required.');
	}

To pull this off, things like your HTML form field names will need to follow the
same rules as properties in PHP (alphanumeric and _ starting with a letter). By
default it is case insensitive, so you can send all lowercase from your URL
if you want and then reference them in whateverCase YouChoose to use. That can
be disabled if you are on a performance powertrip.

You can pass any array or object to the constructor or use SetDataset(). Typical
uses would be for _GET and _POST but you could apply it to any named dataset
that needs looked at. You can also change datasets at will, keeping any
predefined filters intact.

#### Creating a new interface.
Wrap any object or array in the OOP interface.

	$input = new Nether\Input\Filter($_POST);


#### Retrieve a value.
Fetch the value from $_POST['myfield'], after running it through any filters we
assigned to the field.

	$val = $input->MyField

#### Set a value.
Note, this will not update the original source array. Writing back to the
dataset will prompt the copy-on-write, so now you have your own unique dataset
and in this example, will not affect the original $_POST['myfield'].

    $input->MyField = 'ten';

#### Set a filter.
You call the field as a method, passing it a callable function with one argument
which is the value.

	$input->MyField(function($text){
		return str_repalce('a','@',$t);
	});


## Installing
Require this package in your composer.json.

	require {
		"netherphp/input": "~1.0.0"
	}

Then install it or update into it.

	$ composer install --no-dev
	$ composer update --no-dev


## Testing
This library uses Codeception for testing. Composer will handle it for you. Install or Update into it.

	$ composer install --dev
	$ composer update --dev

Then run the tests.

	$ php vendor/bin/codecept run unit
	$ vendor\bin\codecept run unit


