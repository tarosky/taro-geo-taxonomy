<?php

namespace Taro\Common\Pattern;


/**
 * Model class
 *
 * @package Taro\Common\Pattern
 * @property-read \wpdb $db
 * @property-read string $option_key
 * @property-read string $table
 * @method null|string get_var() get_var(string $sql) Do wpdb->get_var
 * @method null|\stdClass get_row() get_row(string $sql) Do wpdb->get_row
 * @method array get_results() get_results(string $sql) Do wpdb->get_results
 * @method int query() query(string $sql) Do wpdb->query
 */
class Model extends Application
{

	/**
	 * Table version
	 *
	 * If false, table will be never created
	 *
	 * @var string|false
	 */
	protected $version = false;

	/**
	 * Table name without prefix
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Override this for placeholder
	 *
	 * @var array
	 */
	protected $where_clause = array();

	/**
	 * Create SQL
	 *
	 * Override this if you need any extra table
	 *
	 * @return string
	 */
	protected function create_sql(){
		return '';
	}

	/**
	 * Update DB
	 *
	 * @return bool
	 */
	protected function do_update(){
		$sql = $this->create_sql();
		if( !$sql ){
		 	trigger_error($this->i18n->s('モデル%sにはSQLが実装されていません。'), get_called_class());
			return false;
		}
		require_once ABSPATH.'wp-admin/includes/upgrade.php';
		dbDelta($sql);
		update_option($this->option_key, $this->version);
		return true;
	}

	/**
	 * Detect if table requires update
	 *
	 * @return bool
	 */
	protected function require_update(){
		if( !$this->version ){
			return false;
		}
		if( !$this->table_exists() ){
			return true;
		}
		$current_version = get_option($this->option_key, '0.0');
		return version_compare($this->version, $current_version, '>') || !$this->table_exists();
	}

	/**
	 * Check db and update if possible
	 */
	public function check_update(){
		if( $this->require_update() && $this->do_update() ){
			$msg = $this->i18n->s('テーブル<code>%s</code>が作成されました。', $this->table);
			add_action('admin_notices', function() use ($msg) {
				printf('<div class="updated"><p>%s</p></div>', $msg);
			});
		}
	}

	/**
	 * Detect if table exists
	 *
	 * @return bool
	 */
	public function table_exists(){
		return (bool) $this->get_row("SHOW TABLES LIKE %s", $this->table);
	}

	/**
	 * Register db update action
	 */
	public static function register(){
		$instance = self::get_instance();
		if( !defined('DOING_AJAX') || !DOING_AJAX ){
			add_action('admin_init', array($instance, 'check_update'));
		}
	}

	/**
	 * Detect if utf8mb4 is supported
	 *
	 * @return bool
	 */
	protected function has_utf8mb4(){
		return (bool)$this->get_row("SHOW CHARACTER SET LIKE 'utf8mb4'");
	}

	/**
	 * Insert data
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function insert($data){
		$where_array = $this->get_where_array($data);
		return (int) $this->db->insert($this->table, $data, $where_array);
	}

	/**
	 * Update data
	 *
	 * @param array $data
	 * @param array $where
	 *
	 * @return int
	 */
	public function update(array $data, array $where){
		$dara_pl = $this->get_where_array($data);
		$where_pl = $this->get_where_array($where);
		return (int) $this->db->update($this->table, $data, $where, $dara_pl, $where_pl);
	}

	/**
	 * Get where clause
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function get_where_array( array $data = array() ){
		$place_holders = array();
		foreach( $data as $column => $value ){
			if( isset($this->where_clause[$column]) ){
				$place_holders[] = $this->where_clause[$column];
			}else{
				$place_holders[] = '%s';
			}
		}
		return $place_holders;
	}

	/**
	 * Magic method overrode
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments){
		switch( $name ){
			case 'get_var':
			case 'get_row':
			case 'get_results':
			case 'query':
				if( 2 > count($arguments) ){
					return call_user_func_array(array($this->db, $name), $arguments);
				}else{
					return call_user_func_array(array($this->db, $name), array(call_user_func_array(array($this->db, 'prepare'), $arguments)));
				}
				break;
			default:
				// Do nothing
				return null;
				break;
		}
	}

	/**
	 *
	 *
	 * @param string $name
	 *
	 * @return null|string|static
	 */
	public function __get($name){
		switch( $name ){
			case 'option_key':
				return strtolower(str_replace('\\', '_', get_called_class())).'_version';
				break;
			case 'db':
				global $wpdb;
				return $wpdb;
				break;
			case 'table':
				return $this->db->prefix.$this->name;
				break;
			default:
				return parent::__get($name);
				break;
		}
	}

}