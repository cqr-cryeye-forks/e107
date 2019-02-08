<?php
/**
 * Created by PhpStorm.
 * Date: 2/8/2019
 * Time: 12:13 PM
 */


	/**
	 * Legacy e107 database methods
	 * Trait e_db_legacy
	 */
	trait e_db_legacy
	{

		public function db_Select($table, $fields = '*', $arg = '', $mode = 'default', $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->select($table, $fields, $arg, $mode !== 'default', $debug, $log_type, $log_remark);
		}


		public function db_Insert($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->insert($tableName, $arg, $debug, $log_type, $log_remark);
		}

		function db_Update($tableName, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->update($tableName, $arg, $debug, $log_type, $log_remark);
		}


		public function db_Close()
		{
			$this->close();
		}


		public function db_Fetch($type = null)
		{
			return $this->fetch($type);
		}


		public function db_Delete($table, $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->delete($table, $arg, $debug, $log_type, $log_remark);
		}


		function db_Replace($table, $arg, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->replace($table, $arg, $debug, $log_type, $log_remark);
		}


		function db_Count($table, $fields = '(*)', $arg = '', $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->count($table, $fields, $arg, $debug, $log_type, $log_remark);
		}


		function db_Rows()
		{
			return $this->rowCount();
		}



		public function db_Select_gen($query, $debug = false, $log_type = '', $log_remark = '')
		{
			return $this->gen($query, $debug, $log_type, $log_remark);
		}


		public function db_Table_exists($table,$language='')
		{
			return $this->isTable($table, $language);
		}


		public function db_TableList($mode='all')
		{
			return $this->tables($mode);
		}





	}