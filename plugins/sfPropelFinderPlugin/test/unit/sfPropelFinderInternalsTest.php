<?php
/*
 * This file is part of the sfPropelFinder package.
 * 
 * (c) 2007 François Zaninotto <francois.zaninotto@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
You need a model built with a running database to run these tests.
The tests expect a model similar to this one:

    propel:
      article:
        id:          ~
        title:       varchar(255)
        category_id: ~
      article_i18n:
        content:     varchar(255)
      category:
        id:          ~
        name:        varchar(255)
      comment:
        id:          ~
        content:     varchar(255)
        article_id:  ~
        author_id:   ~
      author:
        id:          ~
        name:        varchar(255)

Beware that the tables for these models will be emptied by the tests, so use a test database connection.
*/

// Autofind the first available app environment
$sf_root_dir = realpath(dirname(__FILE__).'/../../../../');
$apps_dir = glob($sf_root_dir.'/apps/*', GLOB_ONLYDIR);
$app = substr($apps_dir[0], 
              strrpos($apps_dir[0], DIRECTORY_SEPARATOR) + 1, 
              strlen($apps_dir[0]));
if (!$app)
{
  throw new Exception('No app has been detected in this project');
}

// -- path to the symfony project where the plugin resides
$sf_path = dirname(__FILE__).'/../../../..';
 
// bootstrap
include($sf_path . '/test/bootstrap/functional.php');

// create a new test browser
$browser = new sfTestBrowser();
$browser->initialize();

// initialize database manager
$databaseManager = new sfDatabaseManager();
$databaseManager->initialize();

// cleanup database
ArticlePeer::doDeleteAll();

$t = new lime_test(5, new lime_output_color());

$t->diag('getColName()');

class myFinder extends sfPropelFinder
{
  public function getColName($phpName, $peerClass = null, $withPeerClass = false, $autoAddJoin = false)
  {
    return parent::getColName($phpName, $peerClass, $withPeerClass, $autoAddJoin);
  }
}
$finder = new myFinder('Article');
$t->is($finder->getColName('Title'), 'article.TITLE', 'getColName() recognizes [column phpName]');
$t->is($finder->getColName('Article_Title'), 'article.TITLE', 'getColName() recognizes [table phpName]_[column phpName]');
$t->is($finder->getColName('Article.Title'), 'article.TITLE', 'getColName() recognizes [table phpName].[column phpName]');

$t->is($finder->getColName('a.Title'), 'article.TITLE', 'getColName() recognizes [table alias].[column phpName]');

$finder = new myFinder('Article b');
$t->is($finder->getColName('b.Title'), 'article.TITLE', 'getColName() recognizes [table alias].[column phpName]');