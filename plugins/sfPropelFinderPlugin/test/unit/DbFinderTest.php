<?php
/*
 * This file is part of the DbFinder package.
 * 
 * (c) 2007 François Zaninotto <francois.zaninotto@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
You need a model built with a running database to run these tests.
The tests expect a model similar to this one:

    # Doctrine model
    connection:    doctrine
    DArticle:
      columns:
        title:       string(255)
        category_id: integer

    # Propel model
    propel:
      article:
        id:          ~
        title:       varchar(255)
        category_id: ~

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

$con = Propel::getConnection();

// cleanup databases
Doctrine_Query::create()->delete()->from('DArticle')->execute();
ArticlePeer::doDeleteAll();

$t = new lime_test(18, new lime_output_color());

$t->diag('from()');

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();

$finder = DbFinder::from('DArticle');
$article = $finder->findOne();
$t->isa_ok($finder, 'DbFinder', 'from() called with a Doctrine class name returns a DbFinder');
$t->is($finder->getType(), DbFinder::DOCTRINE, 'from() called with a Doctrine class name returns a DbFinder with a Doctrine adapter');
$t->ok($article instanceof Doctrine_Record, 'A DbFinder initialized from a Doctrine class returns Doctrine_Record objects');

$article2 = new Article();
$article2->setTitle('foo');
$article2->save();

$finder = DbFinder::from('Article');
$article = $finder->findOne();
$t->isa_ok($finder, 'DbFinder', 'from() called with a Propel class name returns a DbFinder');
$t->is($finder->getType(), DbFinder::PROPEL, 'from() called with a Doctrine class name returns a DbFinder with a Propel adapter');
$t->ok($article instanceof BaseObject, 'A DbFinder initialized from a Propel class returns BaseObject objects');

$finderAsArray = DbFinder::from('Article')->toArray();
$t->is_deeply($finderAsArray, array(array('Id' => 1, 'Title' => 'foo', 'CategoryId' => null)), 'toArray() executes the finder and returns an array with column phpNames as keys');

$finderAsString = (string) DbFinder::from('Article');
$expected = <<<FOO
Article_0:
  Id:        1
  Title:     foo
  CategoryId: 

FOO;
$t->is($finderAsString, $expected, '__toString() executes the finder and returns a string with column phpNames as keys');

$finderAsHtml = DbFinder::from('Article')->toHtml();
$expected = <<<FOO
<table class="DbFinder">
  <tr>
    <th>Id</th>
    <th>Title</th>
    <th>CategoryId</th>
  </tr>
  <tr>
    <td>1</td>
    <td>foo</td>
    <td></td>
  </tr>
</table>

FOO;
$t->is($finderAsHtml, $expected, 'toHTML() executes the finder and returns a string with an HTML table with column phpNames as column headers');

class ArticleFinder extends DbFinder
{
  protected $class = 'Article';
  
  public function published()
  {
    return $this;
  }
}

$finder = DbFinder::from('Article');
$t->ok($finder instanceof ArticleFinder, 'from() returns a custom finder if it exists');
$t->ok($finder instanceof DbFinder, 'from() accepts a finder class name if it exists');
$t->ok($finder->getAdapter() instanceof sfPropelFinder, 'from() accepts a finder class name if it exists');
$t->is($finder->getAdapter()->getClass(), 'Article', 'from() accepts a finder class name if it exists');
try
{
  $finder->published();
  $t->pass('Custom finder methods can be called on a DbFinder initialized with a finder name');
}
catch(Exception $e)
{
  $t->fail('Custom finder methods can be called on a DbFinder initialized with a finder name');
}

$finder = new ArticleFinder();
$t->ok($finder instanceof ArticleFinder, 'A class extending DbFinder works as a finder');
$t->ok($finder->getAdapter() instanceof sfPropelFinder, 'A class extending DbFinder works as a finder');
$t->is($finder->getAdapter()->getClass(), 'Article', 'A class extending DbFinder works as a finder');
try
{
  $finder->published();
  $t->pass('Custom finder methods can be called on a DbFinder child');
}
catch(Exception $e)
{
  $t->fail('Custom finder methods can be called on a DbFinder child');
}
