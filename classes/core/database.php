<?php

class Core_Database
{
	protected $_config = array(
		'servers' => array(),
	);

	protected $_conns = array(); // 存储连接，避免重复连接

	protected $_conn; // 执行SQL会用到的连接

	public function __construct($config)
	{
		foreach ($config as $key=>$val)
		{
			if (isset($this->_config[$key]))
			{
				$this->_config[$key] = $val;
			}
		}
	}

	protected function _conn()
	{
		// 默认取第一组server
		foreach ($this->_config['servers'] as $key => $server)
		{
			if (!isset($this->_conns[$key]))
			{
				$this->_conns[$key] = new PDO($server['dsn'], $server['user'] ,$server['password']);
			}
			$this->_conn = $this->_conns[$key];
			break;
		}
	}

	protected function _route($sql)
	{
		$this->_conn();
		return $sql;
	}

	public function query($sql)
	{
		$sql = $this->_route($sql);
		return $this->_conn->query($sql);
	}
}
