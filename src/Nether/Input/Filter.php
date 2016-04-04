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

	protected
	$Dataset = null;
	/*//
	@type array
	the input source dataset that we will request.
	//*/

	protected
	$DefaultFunction = null;
	/*//
	@type callable
	the default filter that will be applied to all requests that do not have
	a specific filter set.
	//*/

	protected
	$Functions = [];
	/*//
	@type array
	the filters that are to be applied.
	//*/

	protected
	$Case = false;
	/*//
	@type bool
	if this class should be case sensitive on the input keys. by default it is
	false because we often create html url variables with lowercase but want
	to be all pascal or camel in the server side.
	//*/

	////////////////
	////////////////

	public function
	__Construct($Dataset=null,$Opt=null) {
		$Opt = new Nether\Object($Opt,[
			'Case' => false
		]);

		$this->Case = $Opt->Case;

		if($Dataset !== null)
		$this->SetDataset($Dataset);

		return;
	}

	////////////////
	////////////////

	public function
	__Get($Key) {
	/*//
	@argv string Key
	@return mixed|false

	fetch the value from the datastore automatically checking if it exists,
	returning the value if it does. if it does not exist we will return false.
	the false however is not a valid enough test if all you care about is
	knowing that the key existed, because you could store a null in a key. use
	the Exists() method if all you care about is that.
	//*/

		if(!is_array($this->Dataset))
		throw new Exception('No dataset bound to this filter object yet.');

		$Key = $this->PrepareKey($Key);

		// return the value through the filtering method if one was defined.
		if(array_key_exists($Key,$this->Functions))
		return $this->Functions[$Key](
			(array_key_exists($Key,$this->Dataset))?
				($this->Dataset[$Key]):
				(null),
			$Key
		);

		// return the value through the default filter if one was defined.
		if(is_callable($this->DefaultFunction))
		return call_user_func(
			$this->DefaultFunction,
			(array_key_exists($Key,$this->Dataset))?
				($this->Dataset[$Key]):
				(null),
			$Key
		);

		// return the value in the end.
		return (array_key_exists($Key,$this->Dataset))?
			($this->Dataset[$Key]):
			(null);
	}

	public function
	__Set($Key,$Val) {
	/*//
	@argv string Key, mixed Value
	@return mixed
	handle pushing data into the datastore.
	//*/

		return $this->Dataset[$this->PrepareKey($Key)] = $Val;
	}

	public function
	__Call($Key,$Argv) {
	/*//
	@argv string Key, array Args
	//*/

		if(count($Argv) !== 1)
		throw new Exception('only expecting one argument to define a filter.');

		if(!is_callable($Argv[0]))
		throw new Exception('the filter must be callable.');

		$this->Functions[$this->PrepareKey($Key)] = $Argv[0];

		return $this;
	}

	public function
	__Invoke($Key) {
	/*//
	@argv array Input
	@return callable or false
	allow retrieval of a defined callback via the invoke syntax. returns
	boolean false if no callback was defined.

	* callable $this('Something');

	to fetch something that was defined as

	* $this->Something(callable);

	//*/

		$Key = $this->PrepareKey($Key);

		if(array_key_exists($Key,$this->Functions))
		return $this->Functions[$Key];

		return false;
	}

	////////////////
	////////////////

	public function
	Exists($Key) {
	/*//
	@argv string Key
	@return bool
	determine if the specified key exists in the original dataset.
	//*/

		return array_key_exists($this->PrepareKey($Key),$this->Dataset);
	}

	public function
	GetDataset() {
	/*//
	@return array
	//*/

		return $this->Dataset;
	}

	public function
	SetDataset($Input) {
	/*//
	@argv array Input
	@argv object Input
	@return $this
	//*/

		if(is_array($Input) || is_object($Input))
		$this->Dataset = $this->PrepareDataset((array)$Input);

		else
		throw new Exception('Dataset must be an array or object.');

		return $this;
	}

	public function
	GetDefaultFunction() {
	/*//
	@return callable
	//*/

		return $this->DefaultFunction;
	}

	public function
	SetDefaultFunction(callable $Func) {
	/*//
	@argv callable Function
	@return $this
	//*/

		$this->DefaultFunction = $Func;
		return $this;
	}

	////////////////
	////////////////

	protected function
	PrepareKey($Key) {
		if(!$this->Case)
		return strtolower($Key);

		return $Key;
	}

	protected function
	PrepareDataset($Data) {
		if(!$this->Case) {
			$New = [];

			foreach((array)$Data as $Key => $Val)
			$New[strtolower($Key)] = $Val;

			return $New;
		} else {
			return (array)$Data;
		}
	}

}
