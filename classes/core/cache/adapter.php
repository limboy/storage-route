<?php

class Cache_Exception extends Exception {}

abstract class Core_Cache_Adapter
{
	abstract public function get($key);
	abstract public function set($key, $value, $life = null);
	abstract public function delete($key);
}
