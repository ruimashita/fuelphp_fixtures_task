<?php
namespace Fuel\Tasks;

use Fuel;
use Fuel\Core\Cli;
use Fuel\Core\DB;
use Fuel\Core\DBUtil;
use Fuel\Core\Format;

/**
 * Fixtures Task
 *
 * @package Fuel
 * @version 1.1
 * @author  Takuya Wakisaka
 * @license MIT License
 * @link    https://github.com/ruimashita/fuelphp_fixtures_task
 */
class Fixtures
{
	
/**
 * This method gets ran when a valid method name is not used in the command.
 *
 * Usage (from command line):
 *
 * php oil r fixtures
 *
 * @return string
 */
	public static function run()
	{
		return static::help();
	}
	
	
	public static function dump()
	{
		$num = Cli::option('n') ? (int) Cli::option('n') : 5;
		$dir = Cli::option('d') ? Cli::option('d') : APPPATH . 'tests/fixture';
		$env = Cli::option('env') ? Cli::option('env') : Fuel::TEST;
		Fuel::$env = $env;
		
		if (!is_dir($dir))
		{
			if (Cli::option('d')) 
			{
				return Cli::color('No such directory: ' . $dir, 'red');
			}
			else
			{
				mkdir($dir);
			}
		}
		$tables = func_get_args();
		if(empty($tables)){
			$tables = DB::list_tables();
		}
		
		foreach ($tables as $table)
		{
			if($prefix = \DB::table_prefix() )
			{
				$table = substr_replace($table, '', 0, strlen($prefix));
			}
			
			if (DBUtil::table_exists($table))
			{
				$result = DB::select('*')->from($table)->limit($num)->execute();
				$data = $result->as_array();
				$file = $dir . '/' . $table . '.yml';
				$data = Format::forge($data)->to_yaml();
				if (file_exists($file))
				{
					rename($file, $file . '.old');
					echo 'Fixture backed-up: ' . $file . PHP_EOL;
				}
				file_put_contents($file, $data);
				echo 'Fixture created: ' . $file . PHP_EOL;
			}
			else
			{
				echo Cli::color('No such table: ' . $table, 'red') . PHP_EOL;
			}
		}
	}
	
	
	
	public static function load()
	{
		$dir = Cli::option('d') ? Cli::option('d') : APPPATH . 'tests/fixture';
		if ( ! is_dir($dir))
		{
			return Cli::color('No such directory: ' . $dir, 'red');
		}
		$env = Cli::option('env') ? Cli::option('env') : Fuel::TEST;
		Fuel::$env = $env;
		
		$args = func_get_args();
		if(empty($args))
		{
			$tables = DB::list_tables();
		}else{
			$tables = $args;
		}

		
		foreach ($tables as $table)
		{
// read yaml file
			$file_name = $table . '.yml';
			$file = $dir.'/'. $file_name;
			if ( ! file_exists($file))
			{
				if(empty($args))
				{
					continue;
				}
				else
				{
					exit('No such file: ' . $file . PHP_EOL);
				}
			}
			$data = file_get_contents($file);
			$yaml = Format::forge($data, 'yaml')->to_array();

// truncate table
			if (DBUtil::table_exists($table))
			{
				DBUtil::truncate_table($table);
			}
			else
			{
				exit('No such table: ' . $table . PHP_EOL);
			}

// insert data
			foreach ($yaml as $row)
			{
				list($insert_id, $rows_affected) = DB::insert($table)->set($row)->execute();
			}

			echo 'Loaded: ' . $file_name . PHP_EOL;
		}
	}



	public static function help()
	{
		return <<<HELP
 
Usage:
    php oil r fixtures:<command> [<table1> |<table2> |..] [-env=<environment>] [-d=/tmp] [-n=5] 

Commands:
    dump    Create fixtures form database.
    load    Load fixtures into database.

Runtime options:
    -d      directory of fixture (default:  APPPATH/tests/fixture/)
    -env    environment (default: test)
    # with dump command
    -n number of rows in fixtures (default: 5)

Examples:
    php oil r fixtures:dump -env=development
    php oil r fixtures:dump -n=5 -d=/tmp table_name1 table_name2
    php oil r fixtures:load -d=/tmp
    php oil r fixtures:load table_name1 table_name2

HELP;
	}

}


