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
		'Email' => 'asdfjjdjdjdjd',
		'Hostname' => 'd@nkeyk@ng'
	];

	public function testConstruction() {

		$boom = false;
		try { $i = new Nether\Input\Filter(['lol'=>'bbq']); }
		catch(Exception $e) { $boom = true; }
		(new Verify(
			'construct handled array',
			$boom
		))->false();

		(new Verify(
			'construct passed array in',
			count($i->GetDataset())
		))->equals(1);

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
			$input->Username
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
		))->null();

		(new Verify(
			'that the email was invalid',
			$input->Email
		))->null();

		(new Verify(
			'that hostname was sanitised as expected',
			$input->Hostname
		))->equals('dnkeykng');

		return;
	}

	public function testDefaultFilter() {

		$input = (new Nether\Input\Filter(static::$TestDataGood))
		->SetDefaultFunction(function($v){ return str_replace('o','0',$v); })
		->Hostname(function($v){ return str_replace('o','a',$v); });

		(new Verify(
			'default filter took hold of username',
			$input->Username
		))->equals('b0b');

		(new Verify(
			'default filter took hold of email',
			$input->Email
		))->equals('b0b@majdak.net');

		(new Verify(
			'default filter did not hit hostname, rather the hostname filter did',
			$input->Hostname
		))->equals('dankeykang');

		return;
	}

}