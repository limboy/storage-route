<?php

class Core_Cache_Adapter_File extends Cache_Adapter
{
	protected $_config = array(
		'basepath' => '',
		'lifetime' => 3600,
	);

	protected $_driver;

	public function __construct($config)
	{
		foreach ($config as $key=>$val)
		{
			if (isset($this->_config[$key]))
			{
				$this->_config[$key] = $val;
			}
		}

		$this->_driver = new File(array(
			'basepath' => $this->_config['basepath'],
		));
	}

	public function set($key, $value, $lifetime = null)
	{
		empty($lifetime) && $lifetime = $this->_config['lifetime'];

		$data = array(
			'expire' => time() + $lifetime,
			'value' => $value,
		);
		$this->_driver->open($key)->write(serialize($data))->execute();
	}

	public function get($key)
	{
		if ($rs = $this->_driver->open($key)->get()->execute())
		{
			$data = unserialize($rs);
			
			if ($data['expire'] > time())
			{
				return $data['value'];
			}
			else
			{
				$this->_driver->delete($key)->execute();
			}
		}

		return null;
	}

	public function delete($key)
	{
		$this->_driver->delete($key)->execute();
	}
}
