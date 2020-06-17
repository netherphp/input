<?php

namespace
Nether\Input;

use
\Nether    as Nether,
\PHPUnit   as PHPUnit;

use
\Exception as Exception;

class FilterTest
extends PHPUnit\Framework\TestCase {

	static protected
	$TestDataGood = [
		'Username' => 'bob',
		'Email' => 'bob@majdak.net',
		'Hostname' => 'donkeykong'
	];

	static protected
	$TestDataBad = [
		'Username' => 'bob!',
		'Email' => 'asdfjjdjdjdjd',
		'Hostname' => 'd@nkeyk@ng'
	];

	/** @test */
	public function
	TestConstruction() {

		$Boom = FALSE;
		try { $Input = new Nether\Input\Filter(['lol'=>'bbq']); }
		catch(Exception $Error) { $Boom = TRUE; }
		$this->AssertFalse($Boom);
		$this->AssertCount(1,$Input->GetDataset());

		$Boom = FALSE;
		try { $Input = new Nether\Input\Filter((Object)[]); }
		catch(Exception $Error) { $Boom = TRUE; }
		$this->AssertFalse($Boom);

		$Boom = FALSE;
		try { $Input = new Nether\Input\Filter('omfg'); }
		catch(Exception $Error) { $Boom = TRUE; }
		$this->AssertTrue($Boom);

		return;
	}

	/** @test */
	public function
	TestDatasetChange() {

		$Input = new Nether\Input\Filter;

		$Input->SetDataset(static::$TestDataGood);
		$this->AssertEquals('bob',$Input->Username);

		$Input->SetDataset(static::$TestDataBad);
		$this->AssertEquals('bob!',$Input->Username);

		return;
	}

	/** @test */
	public function
	TestInputHandleWithoutFilters() {

		$Input = new Nether\Input\Filter(static::$TestDataGood);

		$this->AssertEquals('bob',$Input->Username,'reading data as expected');
		$this->AssertEquals('bob',$Input->UsErNaMe,'reading data case insensitive');
		$this->AssertNull($Input->TotallyDoesNotExist,'reading data that doesnt exist');

		return;
	}

	/** @test */
	public function
	TestInputHandleWithFilters() {

		// good dataset ////////////////

		$Input = (new Nether\Input\Filter(static::$TestDataGood))
		->Username(function($t){ return ($t === 'bob')?($t):(false); })
		->Email(function($t){ return filter_var($t,FILTER_VALIDATE_EMAIL); })
		->Hostname(function($t){ return preg_replace('/[^a-zA-Z0-9]/','',$t); });

		$this->AssertEquals('bob',$Input->Username,'reading good data through filter');
		$this->AssertEquals('bob@majdak.net',$Input->Email,'reading data data with email filter');
		$this->AssertEquals('donkeykong',$Input->Hostname,'reading good data with regex filter');

		// bad dataset /////////////////

		$Input->SetDataset(static::$TestDataBad);

		$this->AssertFalse($Input->Username,'reading bad data through filter');
		$this->AssertFalse($Input->Email,'reading bad data through email filter');
		$this->AssertEquals('dnkeykng',$Input->Hostname,'reading transformed data through regex filter');

		return;
	}

	/** @test */
	public function
	TestInputHandleWithFunctionWithArgs() {

		$Input = (new Nether\Input\Filter(['Integer'=>1]))
		->Integer(
			function($Val,$Var,$Min=0,$Max=0,$Def=0){
				return filter_var($Val,FILTER_VALIDATE_INT,['options'=>[
					'min_range' => $Min,
					'max_range' => $Max,
					'default'   => $Def
				]]);
			},
			[ 1, 3, 0 ]
		);

		$Input->Integer = 1;
		$this->AssertEquals(1,$Input->Integer);

		$Input->Integer = 2;
		$this->AssertEquals(2,$Input->Integer);

		$Input->Integer = 3;
		$this->AssertEquals(3,$Input->Integer);

		$Input->Integer = 4;
		$this->AssertEquals(0,$Input->Integer);

		$Input->Integer = 'gowron';
		$this->AssertEquals(0,$Input->Integer);

		return;
	}

	/** @test */
	public function
	TestDefaultFilter() {

		$Input = (new Nether\Input\Filter(static::$TestDataGood))
		->SetDefaultFunction(function($Val){ return str_replace('o','0',$Val); })
		->Hostname(function($Val){ return str_replace('o','a',$Val); });

		$this->AssertEquals('b0b',$Input->Username,'transformed data through filter');
		$this->AssertEquals('b0b@majdak.net',$Input->Email,'transformed data through filter');
		$this->AssertEquals('dankeykang',$Input->Hostname,'transformed data through filter');

		return;
	}

}