<?php

/**
 * CakePHP Wordpress Datasource
 *
 * PHP 5
 *
 *
 * Angel S. Moreno : Environment Switching class for CakePHP
 * Copyright 2013, Angel S. Moreno (http://github.com/angelxmoreno)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright	Copyright 2013, Angel S. Moreno (http://github.com/angelxmoreno)
 * @license	MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link https://github.com/angelxmoreno/CakePHP-Wordpress-Datasource CakePHP-Wordpress-Datasource
 *
 * @package       datasources
 * @subpackage    datasources.models.datasources
 * @file	WordpressSource.php
 *
 *  * Create a datasource in your config/database.php
 *  public $wordpress = array(
 *	'datasource' => 'WordpressSource.WordpressSource',
 *	'host' => 'example.com',
 *	'path' => '/xml-rpc.php',
 *	'port' => 80,
 *	'timeout' => 15,
 *	'username' => null,
 *	'password' => null,
 *	'blog_id' => 0
 *  );
 *
 */
App::uses('DataSource', 'Model/Datasource');
App::uses('HttpSocket', 'Network/Http');
App::import('Vendor', 'WordpressSource.IXR_Client', array('file' => 'IXR_Library.php'));

/**
 * WordpressSource
 *
 * Datasource for Wordpress API using XML-RPC
 *
 * @property HttpSocket $_http
 * @property IXR_Client $_client
 */
class WordpressSource extends DataSource {

	/**
	 * Description string for this Data Source.
	 *
	 * @var string
	 */
	public $description = 'Wordpress Datasource';

	/**
	 * HttpSocket Object
	 *
	 * @var object HttpSocket
	 */
	protected $_http;

	/**
	 * IXR_Client Object
	 *
	 * @var object IXR_Client
	 */
	protected $_client;

	/**
	 * The DataSource configuration
	 *
	 * @var array
	 */
	public $config = array();

	/**
	 * Configuration base
	 *
	 * @var array
	 */
	public $_baseConfig = array(
	    'host' => 'example.com',
	    'path' => '/xml-rpc.php',
	    'port' => 80,
	    'timeout' => 15,
	    'username' => null,
	    'password' => null,
	    'blog_id' => 0
	);

	/**
	 * Last error found
	 *
	 * @var array
	 */
	public $error;

	/**
	 * Holds a list of sources (tables) contained in the DataSource snd the
	 * corresponding fields
	 *
	 * @var array
	 */
	protected $_sources = array(
	    'posts' => array(
		'post_id' => array('type' => 'biginteger', 'null' => false, 'default' => null, 'key' => 'primary'),
		'post_title' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'post_date' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'post_date_gmt' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'post_modified' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'post_modified_gmt' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'post_status' => array('type' => 'string', 'null' => false, 'default' => 'publish', 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'post_type' => array('type' => 'string', 'null' => false, 'default' => 'post', 'length' => 20, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		//post_format ?
		'post_name' => array('type' => 'string', 'null' => false, 'length' => 200, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'post_author' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'key' => 'index'),
		'post_password' => array('type' => 'string', 'null' => false, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'post_excerpt' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'post_content' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'post_parent' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'key' => 'index'),
		'post_mime_type' => array('type' => 'string', 'null' => false, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		//link ?
		'guid' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'menu_order' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'comment_status' => array('type' => 'string', 'null' => false, 'default' => 'open', 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'ping_status' => array('type' => 'string', 'null' => false, 'default' => 'open', 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		//sticky ?
		/*
		'to_ping' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'pinged' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'post_content_filtered' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'comment_count' => array('type' => 'biginteger', 'null' => false, 'default' => '0'),
		'indexes' => array(
		    'PRIMARY' => array('column' => 'ID', 'unique' => 1),
		    'post_name' => array('column' => 'post_name', 'unique' => 0),
		    'type_status_date' => array('column' => array('post_type', 'post_status', 'post_date', 'ID'), 'unique' => 0),
		    'post_parent' => array('column' => 'post_parent', 'unique' => 0),
		    'post_author' => array('column' => 'post_author', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
		*/
	    ),
	    'taxonomies',
	    'comments',
	    'users'
	);

	/**
	 * List of valid methods when calling the Wordpress XML-RPC Server
	 *
	 * @var array
	 */
	protected $_methods = array(
	    //Posts
	    'wp.getPost',
	    'wp.getPosts',
	    'wp.newPost',
	    'wp.editPost',
	    'wp.deletePost',
	    'wp.getPostType',
	    'wp.getPostTypes',
	    'wp.getPostFormats',
	    'wp.getPostStatusList',
	    //Taxonomies
	    'wp.getTaxonomy',
	    'wp.getTaxonomies',
	    'wp.getTerm',
	    'wp.getTerms',
	    'wp.newTerm',
	    'wp.editTerm',
	    'wp.deleteTerm',
	    //Media
	    'wp.getMediaItem',
	    'wp.getMediaLibrary',
	    'wp.uploadFile',
	    //Comments
	    'wp.getCommentCount',
	    'wp.getComment',
	    'wp.getComments',
	    'wp.newComment',
	    'wp.editComment',
	    'wp.deleteComment',
	    'wp.getCommentStatusList',
	    //Options
	    'wp.getOptions',
	    'wp.setOptions',
	    //Users
	    'wp.getUsersBlogs',
	    'wp.getUser',
	    'wp.getUsers',
	    'wp.getProfile',
	    'wp.editProfile',
	    'wp.getAuthors',
	);

	/**
	 * Default Constructor
	 *
	 * @param array $config options
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->_client = new IXR_Client($this->config['host'], $this->config['path'], $this->config['port'], $this->config['timeout']);
	}

	/**
	 * Returns a list of sources available in this datasource.
	 *
	 * @param mixed $data
	 * @return array Array of sources available in this datasource.
	 */
	public function listSources($data = null) {
		return array_keys($this->_sources);
	}

	/**
	 * Gets full table name including prefix
	 *
	 * @param Model|string $model Either a Model object or a string table name.
	 * @param boolean $quote Whether you want the table name quoted.
	 * @param boolean $schema Whether you want the schema name included.
	 * @return string Full quoted table name
	 */
	public function fullTableName($model) {
		if (is_object($model)) {
			$table = $model->tablePrefix . $model->table;
		} else {
			$table = strval($model);
		}
		return $table;
	}

	/**
	 * Returns an array of the fields in given table name.
	 *
	 * @param Model|string $model Name of database table to inspect or model instance
	 * @return array Fields in table. Keys are name and type
	 * @throws CakeException
	 */
	public function describe($model) {
		$table = $this->fullTableName($model);
		return $this->_sources[$table];
	}

	/**
	 * Perform a XML RPC call
	 *
	 * @param string $method compatible XML-RPC WordPress API method
	 * @param array $params parameters to query
	 * @return mixed Response of Wordpress XML-RPC Server. If return false, $this->error contain a error message.
	 */
	public function query($method, $params = array()) {
		$args = func_get_args();
		$method = array_shift($args);
		if (!in_array($method, $this->_methods)) {
			$this->_setError(900, 'The method ' . (string) $method . ' is not a valid method');
			return false;
		}
		if (!call_user_func_array(array($this->_client, 'query'), array_merge_recursive(array($method, '', $this->config['username'], $this->config['password']), $args))) {
			$this->_setError($this->_client->getErrorCode(), $this->_client->getErrorMessage());
			return false;
		}
		return $this->_client->getResponse();
	}

	public function parseResponse(Array $response) {
		$_response = null;
		foreach ($response as $key => $val) {
			if ($val instanceof IXR_Date) {
				$_response[$key] = date('Y-m-d H:i:s', $val->getTimestamp());
			} elseif (is_array($val) || is_object($val)) {
				$_response[$key] = $this->parseResponse($val);
			} else {
				$_response[$key] = $val;
			}
		}
		return $_response;
	}

	/**
	 * Set a error message and number
	 *
	 * @param integer $errno Number of error
	 * @param string $errmsg Description of error
	 * @return boolean Always false
	 */
	protected function _setError($errno, $errmsg) {
		$this->error = array(
		    'message' => $errmsg,
		    'errno' => $errno
		);
		throw new CacheException($errmsg, $errno);
		return false;
	}
}
