<?php

class Core_Cache_Adapter_Memcache extends Cache_Adapter
{
	protected $_config = array(
		'expire' => 3600,
		'compress' => FALSE,
		'servers' => array(),
	);

	protected $_memcache;

	protected function _route($key) {}

	public function __construct($config)
	{
		foreach ($config as $key=>$val)
		{
			if (isset($this->_config[$key]))
			{
				$this->_config[$key] = $val;
			}
		}

		$server_config = array(
			'host' => 'localhost',
			'port' => 11211,
			'persistent' => FALSE,
			'weight' => 1,
			'timeout' => 1,
			'retry_interval' => 15,
			'status' => TRUE,
			'failure_callback' => NULL,
		);

		$this->_memcache = new Memcache();

		foreach($this->_config['servers'] as $server) {
			$server += $server_config;

			if(!$this->_memcache->addServer($server['host'], $server['port'], $server['persistent'], $server['weight'], $server['timeout'], $server['retry_interval'], $server['status'], $server['failure_callback'])) {
				throw new Cache_Exception('Memcache could not connect to host \':host\' using port \':port\'', array(':host' => $server['host'], ':port' => $server['port']));
			}
		}
	}

	public function get($key)
	{
		$this->_route($key);
		return $this->_memcache->get($key);
	}

	public function set($key, $value, $expire = null)
	{
		$this->_route($key);
		$expire = empty($expire) ? $this->_config['expire'] : $expire;
		return $this->_memcache->set($key, $value, $this->_config['compress'], $expire);
	}

	public function delete($key)
	{
		$this->_route($key);
		return $this->_memcache->delete($key, $timeout);
	}
}
