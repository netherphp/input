<?php

namespace Nether\Input;
use \Nether;
use \Exception;

class Filter {
/*//
this class provides a base filtering system for input data. it allows you to
define a source and then define specific filtering mechanisms for each field
if you so choose.

	$input = (new Nether\Input\Filter($_POST))
	->Email(function($t){ return filter_var($t,FILTER_VALIDATE_EMAIL); })
	->Hostname(function($t){ return preg_replace('//','',$t); });

	if(!$input->Email) {
		die('you need to supply a valid email, jerkface.');
	}

//*/

	protected $Dataset;
	/*//
	@type array
	the input source dataset that we will request.
	//*/

	protected $Functions = [];
	/*//
	@type array
	the filters that are to be applied.
	//*/

	protected $Case = false;
	/*//
	@type bool
	if this class should be case sensitive on the input keys. by default it is
	false because we often create html url variables with lowercase but want
	to be all pascal or camel in the server side.
	//*/

	////////////////
	////////////////

	public function __construct($dataset=null,$opt=null) {
		$opt = new Nether\Object($opt,[
			'Case' => false
		]);

		$this->Case = $opt->Case;

		if($dataset !== null)
		$this->SetDataset($dataset);

		return;
	}

	////////////////
	////////////////

	public function __get($k) {
	/*//
	@argv string Key
	@return mixed|false

	fetch the value from the datastore automatically checking if it exists,
	returning the value if it does. if it does not exist we will return false.
	the false however is not a valid enough test if all you care about is
	knowing that the key existed, because you could store a null in a key. use
	the Exists() method if all you care about is that.
	//*/

		$k = $this->PrepareKey($k);

		// return false if we do not even have it.
		if(!array_key_exists($k,$this->Dataset))
		return false;

		// return the value through the filtering method if one was defined.
		if(array_key_exists($k,$this->Functions))
		return $this->Functions[$k]($this->Dataset[$k]);

		// return the value in the end.
		return $this->Dataset[$k];
	}

	public function __set($k,$v) {
	/*//
	@argv string Key, mixed Value
	@return mixed
	handle pushing data into the datastore.
	//*/

		return $this->Dataset[$this->PrepareKey($k)] = $v;
	}

	public function __call($k,$a) {
	/*//
	@argv string Key, array Args
	//*/

		if(count($a) !== 1)
		throw new Exception('only expecting one argument to define a filter.');

		if(!is_callable($a[0]))
		throw new Exception('the filter must be callable.');

		$this->Functions[$this->PrepareKey($k)] = $a[0];

		return $this;
	}

	////////////////
	////////////////

	public function Exists($k) {
	/*//
	@argv string Key
	@return bool
	determine if the specified key exists in the original dataset.
	//*/

		return array_key_exists($this->PrepareKey($k),$this->Dataset);
	}

	public function GetDataset() {
	/*//
	@return array
	//*/

		return $this->Dataset;
	}

	public function SetDataset($input) {
	/*//
	@argv array Input
	@argv object Input
	@return $this
	//*/

		if(is_array($input) || is_object($input)) {
			$this->Dataset = $this->PrepareDataset((array)$input);
		} else {
			throw new Exception('Dataset must be an array or object.');
		}

		return $this;
	}

	////////////////
	////////////////

	protected function PrepareKey($k) {
		if(!$this->Case) return strtolower($k);
		else return $k;
	}

	protected function PrepareDataset($data) {
		if(!$this->Case) {
			$newdata = [];

			foreach((array)$data as $k => $v)
			$newdata[strtolower($k)] = $v;

			return $newdata;
		} else {
			return (array)$data;
		}
	}

}
