<?php

namespace Nether\Input;
use \Nether;

use \Codeception\Verify;
use \Exception;

class Filter_Test extends \Codeception\TestCase\Test {

	static $TestDataGood = [
		'Username' => 'bob',
		'Email' => 'bob@majdak.net',
		'Hostname' => 'donkeykong'
	];

	static $TestDataBad = [
		'Username' => 'bob!',
		'email' => 'asdfjjdjdjdjd',
		'Hostname' => 'd@nkeyk@ng'
	];

	public function testConstruction() {

		$boom = false;
		try { $i = new Nether\Input\Filter([]); }
		catch(Exception $e) { $boom = true; }
		(new Verify(
			'construct handled array',
			$boom
		))->false();

		(new Verify(
			'construct passed array in',
			is_array($i->GetDataset())
		))->true();

		$boom = false;
		try { $i = new Nether\Input\Filter((object)[]); }
		catch(Exception $e) { $boom = true; }
		(new Verify(
			'construct handled object',
			$boom
		))->false();

		$boom = false;
		try { $i = new Nether\Input\Filter('derp'); }
		catch(Exception $e) { $boom = true; }
		(new Verify(
			'construct refused string',
			$boom
		))->true();

		return;
	}

	public function testDatasetChange() {

		$input = new Nether\Input\Filter;

		$input->SetDataset(static::$TestDataGood);
		(new Verify(
			'first dataset is set',
			$input->Username
		))->equals('bob');

		$input->SetDataset(static::$TestDataBad);
		(new Verify(
			'second dataset is set',
			$input->Username
		))->equals('bob!');

		return;
	}

	public function testInputHandleWithoutFilters() {

		$input = new Nether\Input\Filter(static::$TestDataGood);

		(new Verify(
			'accessing a key as it was given works',
			$input->username
		))->equals('bob');

		(new Verify(
			'accessing a key expecting case insensitivity working',
			$input->UsErNaMe
		))->equals('bob');

		return;
	}

	public function testInputHandleWithFilters() {

		// good dataset ////////////////

		$input = (new Nether\Input\Filter(static::$TestDataGood))
		->Username(function($t){ return ($t === 'bob')?($t):(false); })
		->Email(function($t){ return filter_var($t,FILTER_VALIDATE_EMAIL); })
		->Hostname(function($t){ return preg_replace('/[^a-zA-Z0-9]/','',$t); });

		(new Verify(
			'that bob equals bob',
			$input->Username
		))->equals('bob');

		(new Verify(
			'that the email is valid',
			$input->Email
		))->equals('bob@majdak.net');

		(new Verify(
			'that the hostname only has valid characters',
			$input->Hostname
		))->equals('donkeykong');

		// bad dataset /////////////////

		$input->SetDataset(static::$TestDataBad);

		(new Verify(
			'that the username failed to equal bob',
			$input->Username
		))->false();

		(new Verify(
			'that the email was invalid',
			$input->Email
		))->false();

		(new Verify(
			'that hostname was sanitised as expected',
			$input->Hostname
		))->equals('dnkeykng');

		return;
	}

}