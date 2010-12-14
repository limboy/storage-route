<?php

define('DATA_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR);

spl_autoload_register('autoload');

function autoload($classname)
{
	$class_path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'classes';
	$class_path = $class_path.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, strtolower($classname)).'.php';
	if (file_exists($class_path))
	{
		require $class_path;
		return TRUE;
	}
	return FALSE;
}



//******************************* 自定义文件的存储规则 ******************************
class File extends Core_File
{
	/**
	 * 自定义文件存储规则，对使用者是透明的。
	 */
	protected function _route()
	{
		$pathinfo = pathinfo($this->_filename);
		$extension = empty($pathinfo['extension']) ? '' : '.'.$pathinfo['extension'];
		$fhn = md5($pathinfo['filename']);
		$filepath = $this->_config['basepath'].$fhn[0].$fhn[1].DIRECTORY_SEPARATOR.$fhn[2].$fhn[3].DIRECTORY_SEPARATOR;
		if (!is_dir($filepath))
		{
			mkdir($filepath, 0777, true);
		}
		$this->_filename = $filepath.$fhn.$extension;
	}
}


//******************************* 自定义memcache的连接规则 ******************************
class Cache_Adapter_Memcache extends Core_Cache_Adapter_Memcache
{
	/**
	 * 自定义规则，根据key来指定memcache服务器，如果不指定的话，memcache会自动从连接池中取一个连接。
	 */
	protected function _route($key)
	{
		if(substr($key, 0, 4) == 'sess')
		{
			$server1 = $this->_config['server1'];
			$this->_memcache->connect($server1['host'], $server1['port'], $server1['timeout']);
		}
	}
}


//******************************* 自定义数据库的连接规则 ******************************
class Database extends Core_Database
{
	/**
	 * 根据SQL的内容，选择不同的数据库服务器，不同的数据库，不同的表
	 */
	protected function _route($sql)
	{
		$sql = strtolower($sql);
		if (strpos($sql, 'from user') !== FALSE)
		{
			preg_match('/`id` = ([0-9]+)/', $sql, $match);
			if (!empty($match[1]))
			{
				$user_id = $match[1];
				if ($user_id > 10000)
				{
					$tbl = 'user1';
				}
				$sql = str_replace('from user', 'from user1', $sql);
			}
		}
		// 如果需要连接到其他的数据库服务器，重写_conn方法
		$this->_conn();
		return $sql;
	}
}

//******************************* file demo ******************************
$file = new File(array(
	'basepath' => DATA_PATH.'file'.DIRECTORY_SEPARATOR,
));
$file->open('test.txt')->write('hello world')->execute() ;
echo $file->open('test.txt')->get()->execute();
//***********************************************************************/




//******************************* cache demo *****************************
// file cache
$cache = new Cache_Adapter_File(array('basepath' => DATA_PATH.'cache'.DIRECTORY_SEPARATOR));
$cache->set('foo', 'bar');
echo $cache->get('foo');

// memcache cache
$cache = new Cache_Adapter_Memcache(array(
	'servers' => array(
		'server1' => array(
			'host' => 'localhost',
			'port' => 11211,
			'persistent' => false,
		),
		//array ('server2' => array(
		//	'host' => '192.168.1.100',
		//	'port' => 11211,
		//	'persistent' => false,
		//),
	)
));

$cache->set('bar', 'foo');
echo $cache->get('bar');
//***********************************************************************/




//******************************* database demo *****************************
$db = new Database(array(
		'servers' => array(
			'server1' => array(
				'dsn' => 'mysql:dbname=test;host=127.0.0.1',
				'user' => 'root',
				'password' => '123456',
			),
			//'server2' => array(
			//	'dsn' => 'mysql:dbname=test;host=192.168.1.100',
			//	'user' => 'root',
			//	'password' => '123456',
			//),
		),
	)
);

// 实际情况中，SQL都是通过Query Builder或ORM在内部拼接而成的
$rows = $db->query('SELECT * FROM user WHERE `id` = 100000');

foreach ($rows as $row)
{
	echo $row['username'];
}
//***********************************************************************/
