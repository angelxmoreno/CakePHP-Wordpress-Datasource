CakePHP-Wordpress-Datasource
============================

CakePHP 2.x plugin for accessing Wordpress via XML-RPC.

## Status ##
As of the latest commit, the datasource allows the following functions:

1. getPost($post_id, array $fields = array()) Retrieve a post
1. getPosts(array $filter = array(), array $fields = array()) Retrieve list of posts
1. newPost(array $post) Create a new post
1. editPost($post_id, array $post) Edit an existing post
1. deletePost($post_id) Delete an existing post

More functions coming soon. I plan to have all the methods in http://codex.wordpress.org/XML-RPC_WordPress_API

## Todos ##
1. More detailed installation instructions
1. Attempted to use CakePHP's unofficial Xmlrpc Source (https://github.com/cakephp/datasources/blob/2.0/Model/Datasource/XmlrpcSource.php) by extending it
1. Create Post, Taxonomy, Comment and Users models inside the plugin
1. Add more helper functions based on http://codex.wordpress.org/XML-RPC_WordPress_API
1. Add the CRUD methods for better integration with models

## Requirements ##
* PHP version: PHP 5.2+
* CakePHP version: Cakephp 2.x

## Installation ##
1. Copy the plugin to your application plugins folder. Make sure the directory name is "WordpressSource"
1. Create a new datasource entry in your Config/database.php

        public $wp = array(
                'datasource' => 'WordpressSource.WordpressSource',
                'host' => 'mywordpress.com',
                'path' => '/xmlrpc.php',
                'username' => 'admin',
                'password' => '*******',
                'blog_id' => 0
        );

1. Add this line to the bottom of your app's Config/bootstrap.php

        CakePlugin::load('WordpressSource', array('routes' => false, 'bootstrap' => false));

1. Make sure XML-RPC is turned on in your Wordpress installation. This is enabled by default in versions 3.5+
1. In your controller load the datasource directly

        $this->WP = ConnectionManager::getDataSource('wp');

1. You now have access to the helper functions. See Examples for more details

## Examples ##
1. Get the title of the last 5 modified blog posts that are published

        $res = $this->WP->getPosts(array(
                'post_status' => 'publish',
                'post_type' => 'post',
                'number' => 5,
                'orderby' => 'post_modified',
                'order' => 'desc',
        ), array(
                'post_title'
        ));
        debug($res);

## Branch Strategy ##
Branch is currently unstable. Once I work out the kinks in the Post model I'll create a development branch.
