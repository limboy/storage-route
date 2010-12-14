<?php

class File_Exception extends Exception {}

class Core_File {

	protected $_filename;

	protected $_fp;

	protected $_data;

	protected $_type;

	protected $_config = array(
		'basepath' => '',
		'mode' => 0644,
	);

	protected function _route() {}

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

	public function open($filename) 
	{
		$this->_filename = $filename;
		$this->_route();

		return $this;
	}

	public function create($filename, $mode = null)
	{
		$this->_filename = $filename;
		$this->_route();
		$this->_type = 'create';
		$this->_mode = empty($mode) ? $this->_config['mode'] : $mode;

		return $this;
	}

	public function write($data)
	{
		if(empty($this->_filename)) {
			throw new File_Exception('请先用open方法指定文件名');
		}

		$this->_type = 'write';
		$this->_data = $data;

		return $this;
	}

	public function get()
	{
		if(empty($this->_filename))
		{
			throw new File_Exception('请先用open方法指定文件名');
		}

		$this->_type = 'get';

		return $this;
	}

	public function delete($filename)
	{
		$this->_filename = $filename;
		$this->_route();
		$this->_type = 'delete';

		return $this;
	}

	public function exists($filename)
	{
		$origin_filename = $this->_filename;
		$this->_filename = $filename;
		$this->_route();
		$status = file_exists($this->_filename);
		$this->_filename = $origin_filename;

		return $status;
	}

	public function get_filename($filename)
	{
		$origin_filename = $this->_filename;
		$this->_filename = $filename;
		$this->_route();
		$return = $this->_filename;
		$this->_filename = $origin_filename;

		return $return;
	}

	public function execute()
	{
		if ($this->_type == 'delete')
		{
			if (file_exists($this->_filename))
			{
				$status = @unlink($this->_filename);
				if (!$status)
				{
					$status = FALSE;
				}
				else
				{
					clearstatcache();
				}
			}
			else
			{
				$status = 'file not found';
			}

		}
		elseif ($this->_type == 'get')
		{
			$fp = @fopen($this->_filename, 'r');

			if (!$fp)
			{
				$status = FALSE;
			}
			else
			{
				flock($fp, LOCK_SH);
				$status = fread($fp, filesize($this->_filename));
				flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		elseif ($this->_type == 'write')
		{
			$fp = @fopen($this->_filename, 'w');

			if (!$fp)
			{
				$status = FALSE;
			} 
			else 
			{
				flock($fp, LOCK_EX);
				$status = fwrite($fp, $this->_data);
				flock($fp, LOCK_UN);
				fclose($fp);
				clearstatcache();
			}

		}
		elseif ($this->_type == 'create')
		{
			if (!file_exists($this->_filename))
			{
				if (!touch($this->_filename))
				{
					$status = FALSE;
				}
				else 
				{
					chmod($this->_filename, (int) $this->_mode);
					$status = TRUE;
					clearstatcache();
				}
			}
		}
		else
		{
			throw new File_Exception('请指定一种数据操作类型');
		}

		return $status;

	}
}
