<?php
/*
 * This file is part of the sfDoctrineFinder package.
 * 
 * (c) 2007 Fran�ois Zaninotto <francois.zaninotto@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
You need a model built with a running database to run these tests.
The tests expect a model similar to this one:

    connection:    doctrine
    DArticle:
      columns:
        title:       string(255)
        category_id: integer
      relations:
        Category:
          class:    DCategory
          local:    category_id
          type:     one
          foreign:  id
          foreignAlias: Articles
    DArticleI18n:
      columns:
        id:          { type: integer, primary: true }
        culture:     { type: string(5), primary: true }
        content:     string(255)
    DCategory:
      columns:
        name:        string(255)
    DComment:
      columns:
        content:     string(255)
        article_id:  integer
        author_id:   integer
    DAuthor:
      columns:
        name:        string(255)

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
Doctrine_Query::create()->delete()->from('DArticle')->execute();

$t = new lime_test(103, new lime_output_color());

$t->diag('find()');

$finder = new sfDoctrineFinder('DArticle');
$articles = $finder->find();
$t->isa_ok($articles, 'Doctrine_Collection', 'find() returns a Doctrine_Collection object');
$t->is(count($articles), 0, 'find() returns an empty collection when no records match');

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();

$finder = new sfDoctrineFinder('DArticle');
$articles = $finder->find();
$article = $articles[0];
$t->is($article->getTitle(), 'foo', 'find() returns a collection of records with array access');
$t->is(get_class($article), 'DArticle', 'find() returns a collection of objects of the requested class');

$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();

$article3 = new DArticle();
$article3->setTitle('foo3');
$article3->save();

$finder = new sfDoctrineFinder('DArticle');
$articles = $finder->find();
$t->is(count($articles), 3, 'find() with no argument returns an array of all the records');
$articles = $articles->getData();
$article = array_shift($articles);
$t->is($article->getTitle(), 'foo', 'find() with no argument returns an array of all the records');
$article = array_shift($articles);
$t->is($article->getTitle(), 'foo2', 'find() with no argument returns an array of all the records');
$article = array_shift($articles);
$t->is($article->getTitle(), 'foo3', 'find() with no argument returns an array of all the records');

$finder = new sfDoctrineFinder('DArticle');
$articles = $finder->find(2);
$t->is(count($articles), 2, 'find() with an argument returns a limited array of records');

$t->diag('findOne()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();

$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findOne();
$t->is($article, null, 'findOne() returns null when no records match');

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();

$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();

$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findOne();
$t->isa_ok($article, 'DArticle', 'findOne() returns a single object');
$t->is($article->getTitle(), 'foo', 'findOne() returns the first object matching the conditions');

$t->diag('findLast() and findFirst()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();

$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findFirst();
$t->is($article, null, 'findFirst() returns null when no records match');

$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findLast();
$t->is($article, null, 'findLast() returns null when no records match');

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();

$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();

$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findFirst();
$t->isa_ok($article, 'DArticle', 'findFirst() returns a single object');
$t->is($article->getTitle(), 'foo', 'findFirst() returns the last object matching the conditions');

$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findLast();
$t->isa_ok($article, 'DArticle', 'findLast() returns a single object');
$t->is($article->getTitle(), 'foo2', 'findLast() returns the last object matching the conditions');

$t->diag('findBy() and findOneBy()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();

$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();

$article3 = new DArticle();
$article3->setTitle('foo');
$article3->save();

$articles = sfDoctrineFinder::from('DArticle')->findBy('Title', 'foo');
$t->is(count($articles), 2, 'findBy() adds a condition on a given column');
foreach ($articles as $article)
{
  $t->is($article->getTitle(), 'foo', 'findBy() adds a condition on a given column');
}

$articles = sfDoctrineFinder::from('DArticle')->findBy('Title', 'foo', 1);
$t->is(count($articles), 1, 'findBy() accepts a limit parameter');

$article = sfDoctrineFinder::from('DArticle')->findOneBy('Title', 'foo2');
$t->is($article->getTitle(), 'foo2', 'findOneBy() adds a condition on a given column');

$articles = sfDoctrineFinder::from('DArticle')->findByTitle('foo');
$t->is(count($articles), 2, 'findByXXX() adds a condition on a given column');
foreach ($articles as $article)
{
  $t->is($article->getTitle(), 'foo', 'findByXXX() adds a condition on a given column');
}

$articles = sfDoctrineFinder::from('DArticle')->findByTitle('foo', 1);
$t->is(count($articles), 1, 'findByXXX() accepts a limit parameter');

$article = sfDoctrineFinder::from('DArticle')->findOneByTitle('foo2');
$t->is($article->getTitle(), 'foo2', 'findOneByXXX() adds a condition on a given column');

$t->diag('findPk()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();

$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();

$article3 = new DArticle();
$article3->setTitle('foo3');
$article3->save();

$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findPk($article2->getId());
$t->is($article->getTitle(), 'foo2', 'findPk() returns the object with the primary key matching the argument');
$t->ok(!is_array($article), 'findPk() returns a single object when passed a single primary key');
$finder = new sfDoctrineFinder('DArticle');
$article = $finder->findPk(76543787654);
$t->is($article, null, 'findPk() returns null if the primary key is not found');
$finder = new sfDoctrineFinder('DArticle');
$articles = $finder->findPk(array($article2->getId(), $article1->getId()));
$t->isa_ok($articles, 'Doctrine_Collection', 'findPk() returns a collection of objects when passed an array of primary keys');
$t->is(count($articles), 2, 'findPk() returns the objects with the primary keys matching the arguments');

$t->skip('findPk() is compatible with with()');
//$article = $finder->with('Category')->findPk($article2->getId());
//$t->cmp_ok(strpos($finder->getLatestQuery(), 'SELECT article.ID, article.VERSION, article.TITLE, article.CATEGORY_ID, category.ID, category.NAME FROM article, category'), '===', 0, 'findPk() is compatible with with()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();
$articlei18n1 = new DArticleI18n();
$articlei18n1->setCulture('fr');
$articlei18n1->setContent('Bar');
$articlei18n1->setId($article1->getId());
$articlei18n1->save();
try
{
  $articleI18n = sfDoctrineFinder::from('DArticleI18n')->findPk($articlei18n1->getId());
  $t->fail('findPk() expects an array of values for objects with composite primary keys');
}
catch(Exception $e)
{
  $t->pass('findPk() expects an array of values for objects with composite primary keys');
}
$articleI18n = sfDoctrineFinder::from('DArticleI18n')->findPk(array($articlei18n1->getId(), $articlei18n1->getCulture()));
$t->is_deeply($articleI18n->toArray(), $articlei18n1->toArray(), 'findPk() retrieves objects with composite primary keys based on an array of Pks');

$t->diag('Instanciation possibilities');

Doctrine_Query::create()->delete()->from('DArticle')->execute();

$article1 = new DArticle();
$article1->setTitle('foo');
$article1->save();

$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();

$article3 = new DArticle();
$article3->setTitle('foo3');
$article3->save();

$finder = new sfDoctrineFinder('DArticle');
$t->is($finder->getClass(), 'DArticle', 'Record Class can be set during instanciation, in which case the finder is automatically initialized');
$article = $finder->findOne();
$t->isa_ok($article, 'DArticle', 'A finder instanciated directly with a Record class returns the correct objects');
$t->is($article->getTitle(), 'foo', 'A finder instanciated directly with a Record class returns the correct objects');

$finder = new sfDoctrineFinder();
$finder->setClass('DArticle');
$t->is($finder->getClass(), 'DArticle', 'setClass() and getClass() are accesors to the protected $class property');
$article = $finder->findOne();
$t->isa_ok($article, 'DArticle', 'A finder instanciated directly with a Record class returns the correct objects');
$t->is($article->getTitle(), 'foo', 'A finder can be instanciated without parameter, and initialized later after defining its class');

$articles = sfDoctrineFinder::from('DArticle')->find();
$t->is(count($articles), 3, 'from() allows direct chaining of conditions');

class DArticleFinder extends sfDoctrineFinder
{
  protected $class = 'DArticle';
}
$finder = new DArticleFinder();
$article = $finder->findOne();
$t->isa_ok($article, 'DArticle', 'A finder extending sfDoctrineFinder can be used directly if defining the $class property');
$finder = new DArticleFinder();
$articles = $finder->find();
$t->is(count($articles), 3, 'A finder extending sfDoctrineFinder can be used directly if defining the $class property');

Doctrine_Query::create()->delete()->from('DArticle')->execute();
Doctrine_Query::create()->delete()->from('DCategory')->execute();
$category1 = new DCategory();
$category1->setName('cat1');
$category1->save();
$category2 = new DCategory();
$category2->setName('cat2');
$category2->save();
$article1 = new DArticle();
$article1->setTitle('aaaaa');
$article1->setCategory($category1);
$article1->save();
$article2 = new DArticle();
$article2->setTitle('bbbbb');
$article2->setCategory($category1);
$article2->save();
$article3 = new DArticle();
$article3->setTitle('ccccc');
$article3->setCategory($category2);
$article3->save();

$finder = sfDoctrineFinder::from($category1->getArticles());
$articles = $finder->find();
$t->is(count($articles), 2, 'from() accepts an array of Doctrine objects');
$t->isnt(
  strpos($finder->getLatestQuery(), "WHERE d.id IN ("),
  false,
  'using from() with an array of Propel objects results in a IN'
);
$t->isa_ok($finder->findOne(), 'DArticle', 'using from() with an array of Doctrine objects returns some of these objects');
$articles = sfDoctrineFinder::from($category1->getArticles())->where('Title', 'aaaaa')->find();
$t->is(count($articles), 1, 'A finder initialized from an array accepts further conditions');

$t->diag('count()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();
$finder = new sfDoctrineFinder('DArticle');
$nbArticles = $finder->count();
$t->isa_ok($nbArticles, 'integer', 'count() returns an integer');
$t->is($nbArticles, 0, 'count() returns 0 on empty tables');
$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();
$article3 = new DArticle();
$article3->setTitle('foo3');
$article3->save();
$nbArticles = $finder->count();
$t->is($nbArticles, 2, 'count() returns the number of records matching the condition');
$nbArticles = $finder->whereTitle('foo2')->count();
$t->is($nbArticles, 1, 'count() returns the number of records matching the condition');

$t->diag('delete()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();
$finder = new sfDoctrineFinder('DArticle');
$nbDeleted = $finder->delete();
$t->is($nbDeleted, 0, 'delete() on an empty finder with no results returns 0');
$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();
$article3 = new DArticle();
$article3->setTitle('foo3');
$article3->save();
$finder = new sfDoctrineFinder('DArticle');
$nbDeleted = $finder->delete();
$t->is($nbDeleted, 2, 'delete() deletes records from the table and returns the number of deleted rows');
$finder = new sfDoctrineFinder('DArticle');
$nbArticles = $finder->count();
$t->is($nbArticles, 0, 'delete() on an empty finder deletes all rows');
$article2 = new DArticle();
$article2->setTitle('foo2');
$article2->save();
$article3 = new DArticle();
$article3->setTitle('foo3');
$article3->save();
$finder = new sfDoctrineFinder('DArticle');
$nbDeleted = $finder->where('Title', 'foo2')->delete();
$t->is($nbDeleted, 1, 'delete() deletes all rows found by a finder');
$nbArticles = $finder->count();
$t->is($nbArticles, 1, 'delete() does not delete rows not found by the finder');

$t->diag('where()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();
$article1 = new DArticle();
$article1->setTitle('abc');
$article1->save();
$article2 = new DArticle();
$article2->setTitle('def');
$article2->save();
$article3 = new DArticle();
$article3->setTitle('bbc');
$article3->save();
$article = sfDoctrineFinder::from('DArticle')->where('Title', 'abc')->findOne();
$t->is($article->getId(), $article1->getId(), 'where() accepts a simple CamelCase column name like ClassName');
try
{
  $article = sfDoctrineFinder::from('DArticle')->where('Foo', 'abc')->find();
  $t->fail('where() throws an exception when the column is not found (but only after calling find())');
}
catch (Exception $e)
{
  $t->pass('where() throws an exception when the column is not found (but only after calling find())');
}
$article = sfDoctrineFinder::from('DArticle')->where('DArticle.Title', 'abc')->findOne();
$t->is($article->getId(), $article1->getId(), 'where() accepts a complete column name ClassName.ColumnName');
$article = sfDoctrineFinder::from('DArticle')->where('DArticle_Title', 'abc')->findOne();
$t->is($article->getId(), $article1->getId(), 'where() accepts a complete column name ClassName_ColumnName');
$article = sfDoctrineFinder::from('DArticle')->where('d.Title', 'abc')->findOne();
$t->is($article->getId(), $article1->getId(), 'where() accepts a complete column name ClassShortcut.ColumnName');
$article = sfDoctrineFinder::from('DArticle b')->where('b.Title', 'abc')->findOne();
$t->is($article->getId(), $article1->getId(), 'where() accepts a complete column name ClassAlias.ColumnName');
$article = sfDoctrineFinder::from('DArticle')->where('Title', 'def')->findOne();
$t->is($article->getId(), $article2->getId(), 'where() adds a WHERE condition on the column given as first argument');
$articles = sfDoctrineFinder::from('DArticle')->where('Title', ' LIKE ', '%bc')->find();
$t->is(count($articles), 2, 'where() accepts a comparator as second argument when three arguments are given');
$articles = sfDoctrineFinder::from('DArticle')->where('Title', 'like', '%bc')->find();
$t->is(count($articles), 2, 'where() accepts a text comparator and is permissive on syntax');
$articles = sfDoctrineFinder::from('DArticle')->where('Title', 'is not null', null)->find();
$t->is(count($articles), 3, 'where() accepts a text comparator and is permissive on syntax');
$articles = sfDoctrineFinder::from('DArticle')->where('Title', 'in', array('abc', 'def'))->find();
$t->is(count($articles), 2, 'where() accepts a "in" comparator');
$articles = sfDoctrineFinder::from('DArticle')->where('Title', 'not in', array('abc', 'def'))->find();
$t->is(count($articles), 1, 'where() accepts a "not in" comparator');
try
{
  $article = sfDoctrineFinder::from('DArticle')->whereFoo('abc')->find();
  $t->fail('whereXXX() throws an exception when the XXX column is not found (but only after calling find())');
}
catch (Exception $e)
{
  $t->pass('whereXXX() throws an exception when the XXX column is not found (but only after calling find())');
}

try
{
  $article = sfDoctrineFinder::from('DArticle')->wherecategoryid('abc')->find();
  $t->fail('whereXXX() expects a column name in CamelCase');
}
catch (Exception $e)
{
  $t->pass('whereXXX() expects a column name in CamelCase');
}

try
{
  $article = sfDoctrineFinder::from('DArticle')->whereCategoryId('abc')->find();
  $t->pass('whereXXX() expects a column name in CamelCase');
}
catch (Exception $e)
{
  $t->fail('whereXXX() expects a column name in CamelCase');
}

try
{
  $article = sfDoctrineFinder::from('DArticle')->whereTitle('abc', 'def', 'ghi', 'jkl')->find();
  $t->fail('whereXXX() throws an exception when called with more than three parameters');
}
catch (Exception $e)
{
  $t->pass('whereXXX() throws an exception when called with more than three parameters');
}

$article = sfDoctrineFinder::from('DArticle')->whereTitle('abc')->findOne();
$t->is($article->getId(), $article1->getId(), 'whereXXX() adds a where condition according to the XXX column name');
$article = sfDoctrineFinder::from('DArticle')->whereDArticle_Title('abc')->findOne();
$t->is($article->getId(), $article1->getId(), 'whereXXX() accepts a complete column name like whereClassName_ColumnName()');
$article = sfDoctrineFinder::from('DArticle')->whereTitle('def')->findOne();
$t->is($article->getId(), $article2->getId(), 'whereXXX() adds a WHERE condition on the XXX column');
$articles = sfDoctrineFinder::from('DArticle')->whereTitle('like', '%bc')->find();
$t->is(count($articles), 2, 'whereXXX() accepts a comparator as first argument when two arguments are given');

$t->diag('orWhere()');

$columns    = "d.id AS d__id, d.title AS d__title, d.category_id AS d__category_id";
$baseSelect = "SELECT $columns FROM d_article d WHERE ";

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', 'foo')->
  orWhere('Title', 'bar');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foo' OR d.title = 'bar')",
  'orWhere() adds a SQL OR clause when called on a column where there is already a condition'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', 'foo')->
  orWhere('CategoryId', 1);
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foo' OR d.category_id = '1')",
  'orWhere() adds a SQL OR clause when called on a column where there is no condition yet'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', 'foo')->
  where('Title', 'bar');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foo' AND d.title = 'bar')",
  'where() adds a SQL AND clause when called on a column where there is already a condition'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', 'foo')->
  where('CategoryId', 1);
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foo' AND d.category_id = '1')",
  'where() adds a SQL AND clause when called on a column where there is no condition yet'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('CategoryId', 1)->
  where('Title', 'foo')->
  orWhere('Title', 'bar');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.category_id = '1' AND (d.title = 'foo' OR d.title = 'bar'))",
  'where() and orWhere() can be combined on the same finder'
);

/*
$finder = sfDoctrineFinder::from('DArticle')->
  joinCategory()->
  where('Category.Name', 'foo')->
  orWhere('Category.Name', 'bar');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  "SELECT $columns FROM article, category WHERE (category.NAME='foo' OR category.NAME='bar') AND article.CATEGORY_ID=category.ID",
  'orWhere() works on a simple jointure'
);

$finder = sfDoctrineFinder::from('DComment')->
  joinArticle()->
  joinAuthor()->
  where('Article.Title', 'foo')->
  orWhere('Author.Name', 'bar');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  "SELECT comment.ID, comment.CONTENT, comment.ARTICLE_ID, comment.AUTHOR_ID FROM comment, article, author WHERE (article.TITLE='foo' OR author.NAME='bar') AND comment.ARTICLE_ID=article.ID AND comment.AUTHOR_ID=author.ID",
  'orWhere() works on a multiple jointure'
);
*/

$t->skip('orWhere() works on a simple jointure');
$t->skip('orWhere() works on a multiple jointure');

$t->diag('combine()');

$columns    = "d.id AS d__id, d.title AS d__title, d.category_id AS d__category_id";
$baseSelect = "SELECT $columns FROM d_article d WHERE ";

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "d.title = 'bar'",
  'where() called with a named condition does not affect the SQL until it is combined'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar', 'cond2')->
  combine(array('cond1', 'cond2'), 'or');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foo' OR d.title = 'bar')",
  'combine() combines conditions into the main query'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar', 'cond2')->
  combine(array('cond1', 'cond2'), 'and');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foo' AND d.title = 'bar')",
  'combine() combines conditions into the main criteria'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar', 'cond2')->
  where('Title', '=', 'foobar')->
  combine(array('cond1', 'cond2'), 'or');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foobar' AND (d.title = 'foo' OR d.title = 'bar'))",
  'combine() clauses live well with the usual conditions'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar', 'cond2')->
  combine(array('cond1', 'cond2'), 'or')->
  where('Title', '=', 'foobar');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "((d.title = 'foo' OR d.title = 'bar') AND d.title = 'foobar')",
  'combine() clauses live well with the usual conditions and appear ordered as they were called'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar', 'cond2')->
  where('Title', '=', 'foobar', 'cond3')->
  combine(array('cond1', 'cond2'), 'or', 'cond4')->
  combine(array('cond4', 'cond3'), 'and');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "((d.title = 'foo' OR d.title = 'bar') AND d.title = 'foobar')",
  'combine() can return a named condition'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar', 'cond2')->
  where('Title', '=', 'foobar', 'cond3')->
  where('Title', '=', 'boofar', 'cond4')->
  combine(array('cond1', 'cond2'), 'or', 'cond6')->
  combine(array('cond3', 'cond4'), 'or', 'cond7')->
  combine(array('cond6', 'cond7'), 'and');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "((d.title = 'foo' OR d.title = 'bar') AND (d.title = 'foobar' OR d.title = 'boofar'))",
  'combine() allows for imbricated conditions'
);

$finder = sfDoctrineFinder::from('DArticle')->
  where('Title', '=', 'foo', 'cond1')->
  where('Title', '=', 'bar', 'cond2')->
  where('Title', '=', 'foobar', 'cond3')->
  combine(array('cond1', 'cond2', 'cond3'), 'or');
$finder->find();
$t->is(
  $finder->getLatestQuery(),
  $baseSelect . "(d.title = 'foo' OR d.title = 'bar' OR d.title = 'foobar')",
  'combine() can combine more than two conditions'
);

$t->diag('limit() and offset()');

Doctrine_Query::create()->delete()->from('DArticle')->execute();
$article1 = new DArticle();
$article1->setTitle('abc');
$article1->save();
$article2 = new DArticle();
$article2->setTitle('def');
$article2->save();
$article3 = new DArticle();
$article3->setTitle('bbc');
$article3->save();

$articles = sfDoctrineFinder::from('DArticle')->limit(1)->find();
$t->is(count($articles), 1, 'limit() adds a limit to the SQL clause');
$article = $articles[0];
$t->is($article->getTitle(), 'abc', 'limit() adds a limit to the SQL clause');
$articles = sfDoctrineFinder::from('DArticle')->limit(2)->find();
$t->is(count($articles), 2, 'limit() adds a limit to the SQL clause');
$article = $articles[0];
$t->is($article->getTitle(), 'abc', 'limit() adds a limit to the SQL clause');
$article = $articles[1];
$t->is($article->getTitle(), 'def', 'limit() adds a limit to the SQL clause');

$articles = sfDoctrineFinder::from('DArticle')->offset(1)->find();
$t->is(count($articles), 2, 'offset() adds an offset to the SQL clause');
$article = $articles[0];
$t->is($article->getTitle(), 'def', 'offset() adds an offset to the SQL clause');
$article = $articles[1];
$t->is($article->getTitle(), 'bbc', 'offset() adds an offset to the SQL clause');

$articles = sfDoctrineFinder::from('DArticle')->offset(1)->limit(1)->find();
$t->is(count($articles), 1, 'limit() and offset() can be combined');
$article = $articles[0];
$t->is($article->getTitle(), 'def', 'limit() and offset() can be combined');
