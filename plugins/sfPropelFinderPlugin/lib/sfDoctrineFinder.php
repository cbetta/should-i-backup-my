<?php

/*
 * This file is part of the sfPropelFinder package.
 * 
 * (c) 2007 François Zaninotto <francois.zaninotto@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
class sfDoctrineFinder extends sfModelFinder
{
  protected
    $connection    = null,
    $class         = null,
    $alias         = null,
    $object        = null,
    $reinit        = true,
    $query         = null,
    $queryPattern  = '',
    $queryArgs     = array(),
    $namedPatterns = array(),
    $namedArgs     = array(),
    $argNumber     = 0,
    $queryListener = null,
    $withClasses   = array(),
    $withColumns   = array(),
    $culture       = null;
  
  public function getClass()
  {
    return $this->class;
  }
  
  /**
   * Caution: reinitializes the finder's Doctrine_Query object
   */
  public function setClass($class, $alias = '')
  {
    $this->addRelation($class, $alias);
    $this->class = $class;
    $this->alias = $alias;
    $this->object = new $class();
    $this->initialize($this->connection, $class, $alias);
    
    return $this;
  }

  public function getConnection()
  {
    return $this->connection;
  }

  /**
   * Caution: reinitializes the finder's Doctrine_Query object
   */
  public function setConnection($connection)
  {
    $this->connection = $connection;
    if($this->class)
    {
      $this->initialize($connection, $this->class, $this->alias);
    }
    
    return $this;
  }
  
  public function getQuery()
  {
    return $this->buildQuery();
  }
  
  public function buildquery()
  {
    if($this->queryPattern)
    {
      $this->query->addWhere($this->queryPattern, $this->queryArgs);
      $this->queryPattern = '';
      $this->queryArgs = array();
    }
    
    return $this->query;
  }
  
  // Finder initializers
  
  /**
   * Mixed initializer
   * Accepts either a string (Model object class) or an array of model objects
   *
   * @param mixed $from The data to initialize the finder with
   * @param mixed $connection Optional connection object
   *
   * @return sfPropelFinder a finder object
   * @throws Exception If the data is neither a classname nor an array
   */
  public static function from($from, $connection = null)
  {
    if (is_string($from))
    {
      return self::fromClass($from, $connection);
    }
    if (is_array($from) || $from instanceof Doctrine_Collection)
    {
      return self::fromCollection($from);
    }
    throw new Exception('sfPropelFinder::from() only accepts a model object classname or an array of model objects');
  }
  
  /**
   * Array initializer
   *
   * @param array $array Array of Primary keys
   * @param string $class Model classname on which the search will be done
   *
   * @return sfPropelFinder a finder object
   */
  public static function fromArray($array, $class, $pkName)
  {
    $finder = self::fromClass($class);
    $finder->where($pkName, 'in', $array);
    
    return $finder;
  }
  
  /**
   * Class initializer
   *
   * @param string $from Model classname on which the search will be done
   * @param mixed $connection Optional connection object
   *
   * @return sfDoctrineFinder a finder object
   */
  public static function fromClass($class, $connection = null)
  {
    if(strpos($class, ' ') !== false)
    {
      list($realClass, $alias) = explode(' ', $class);
    }
    else
    {
      $realClass = $class;
    }
    if(class_exists($realClass))
    {
      $tmp = new $realClass;
      if($tmp instanceof Doctrine_Record)
      {
        $me = __CLASS__;
        $finder = new $me($class, $connection);
      }
      else
      {
        throw new Exception('sfDoctrineFinder::fromClass() only accepts a Doctrine model classname');
      }
    }
    else
    {
       throw new Exception('sfDoctrineFinder::fromClass() only accepts a Doctrine model classname');
    }
    
    return $finder;
  }
  
  /**
   * Collection initializer
   *
   * @param array $from Array of model objects of the same class
   * @param string $class Optional classname of the desired objects
   * @param string $class Optional column name of the primary key
   *
   * @return sfDoctrineFinder a finder object
   * @throws Exception If the array is empty, contains not model objects or composite objects
   */
  public static function fromCollection($collection, $class = '', $pkName = '')
  {
    $pks = array();
    foreach($collection as $object)
    {
      if($class != get_class($object))
      {
        if($class)
        {
          throw new Exception('A sfDoctrineFinder can only be initialized from an array of objects of a single class');
        }
        if($object instanceof Doctrine_Record)
        {
          $class = get_class($object);
        }
        else
        {
          throw new Exception('A sfDoctrineFinder can only be initialized from an array of Doctrine objects');
        }
      }
      $identifier = array_values($object->identifier());
      if(count($identifier) == 1)
      {
        $identifier = array_shift($identifier);
      }
      $pks []= $identifier;
    }
    if(!$class)
    {
      throw new Exception('A sfDoctrineFinder cannot be initialized with an empty array');
    }
    $tempObject = new $class();
    foreach ($tempObject->getTable()->getColumns() as $name => $column)
    {
      if(array_key_exists('primary', $column))
      {
        if($pkName)
        {
          throw new Exception('A sfDoctrineFinder cannot be initialized from an array of objects with several foreign keys');
        }
        else
        {
          $pkName = $name;
        }
      }
    }
    
    return self::fromArray($pks, $class, $pkName);
  }
  
  
  public function initialize($connection, $class, $alias)
  {
    $this->query = Doctrine_Query::create($connection)->from($class . ' ' . $alias);
    $this->queryListener = sfDoctrineFinderListener::getInstance();
    
    return $this;
  }
  
  protected function addRelation($class, $alias = '')
  {
    if(!$alias) list($class, $alias) = $this->getClassAndAlias($class);
    $this->relations[$alias] = $class;
  }
  
  public function getObject()
  {
    return $this->object;
  }

  public function getLatestQuery()
  {
    return $this->queryListener->getLatestQuery();
  }
  
  public function updateLatestQuery()
  {
     throw new Exception('This method is not yet implemented');
  }
  
  public function addWithClass($class)
  {
    $this->withClasses []= $class;
    
    return $this;
  }
  
  public function getWithClasses()
  {
    return $this->withClasses;
  }
  
  public function reinitWithClasses()
  {
    $this->withClasses = array();
    
    return $this;
  }

  public function getWithColumns()
  {
    return $this->withColumns;
  }
  
  public function reinitWithColumns()
  {
    $this->withColumns = array();
    
    return $this;
  }
  
  // Finder Executers
  
  /**
   * Returns the number of records matching the finder
   *
   * @param Boolean $distinct Whether the count query has to add a DISTINCT keyword (unsupported)
   *
   * @return integer Number of records matching the finder
   */
  public function count($distinct = false)
  {
    $res = $this->getQuery()->count();
    $this->cleanup();
    
    return $res;
  }
  
  /**
   * Executes the finder and returns the matching Doctrine objects
   *
   * @param integer $limit Optional maximum number of results to retrieve
   *
   * @return array A list of Doctrine_Record objects
   */
  public function find($limit = null)
  {
    if($limit)
    {
      $this->query->limit($limit);
    }
    $res = $this->getQuery()->execute();
    $this->cleanup();
    
    return $res;
  }
  
  /**
   * Limits the search to a single result, and executes the finder
   * Returns the first Doctrine_Record object matching the finder
   *
   * @return mixed a Doctrine_Record object or null
   */
  public function findOne()
  {
    $records = $this->find(1);
    
    return isset($records[0]) ? $records[0] : null;
  }
  
  /**
   * Returns the last record matching the finder
   * Optionally, you can specify the column to sort on
   * If no column is passed, the finder guesses the column to use
   * @see guessOrder()
   *
   * @param string $columnName Optional: The column to order by
   *
   * @return mixed a Doctrine_Record object or null
   */
  public function findLast($columnName = null)
  {
    if($columnName)
    {
      $this->orderBy($columnName, 'desc');
    }
    else
    {
      $this->guessOrder('desc');
    }
    
    return $this->findOne();
  }
  
  /**
   * Returns the first record matching the finder
   * Optionally, you can specify the column to sort on
   * If no column is passed, the finder guesses the column to use
   * @see guessOrder()
   *
   * @param string $columnName Optional: The column to order by
   *
   * @return mixed a Doctrine_Record object or null
   */
  public function findFirst($columnName = null)
  {
    if($columnName)
    {
      $this->orderBy($columnName, 'asc');
    }
    else
    {
      $this->guessOrder('asc');
    }
    
    return $this->findOne();
  }
  
  /**
   * Adds a condition on a column and returns the records matching the finder
   *
   * @param string $columnName Name of the columns
   * @param mixed $value
   * @param integer $limit Optional maximum number of records to return
   *
   * @return array A list of Doctrine_Record Propel objects
   */
  public function findBy($columnName, $value, $limit = null)
  {
    $column = $this->getColName($columnName);
    $this->where($column, '=', $value);
    
    return $this->find($limit);
  }

  /**
   * Adds a condition on a column and returns the first record matching the finder
   * Useful to retrieve objects by a column which is not the primary key
   *
   * @param string $columnName Name of the columns
   * @param mixed $value
   *
   * @return mixed a Doctrine_Record object or null
   */
  public function findOneBy($columnName, $value)
  {
    $column = $this->getColName($columnName);
    $this->where($column, '=', $value);
    
    return $this->findOne();
  }
  
  /**
   * Finds record(s) based on one or several primary keys
   * Takes into account hydrating methods previously called on the finder
   *
   * @param mixed $pk A primary kay, a composite primary key, or an array of primary keys
   *
   * @return mixed One or several Doctrine_Record records (based on the input)
   */
  public function findPk($pk)
  {
    $pkColumns = $this->getObject()->getTable()->getIdentifierColumnNames();
    if(($count = count($pkColumns)) > 1)
    {
      // composite primary key
      if(!is_array($pk))
      {
        throw new Exception(sprintf('Class %s has a composite primary key and expects %s parameters to retrieve a record by pk', $this->class, join(', ', $pkColumns)));
      } 
      else if (is_array($count[0]))
      {
        // array of arrays
        // sorry the finder can't do that on objects with composte primary keys
        throw new Exception('Impossible to find a list of Pks on an objects with composite primary keys');
      }
      for ($i=0; $i < $count; $i++)
      { 
        $this->addCondition('and', $pkColumns[$i], '=', $pk[$i]);
      }
      return $this->findOne();
    }
    else
    {
      // simple primary kay
      if(is_array($pk))
      {
        $this->addCondition('and', $pkColumns[0], ' IN ', $pk);
        return $this->find();
      }
      else
      {
        $this->addCondition('and', $pkColumns[0], '=', $pk);
        return $this->findOne();
      }
    }
  }
  
  /**
   * Deletes the records found by the finder
   * Beware that behaviors based on hooks in the object's delete() method
   * Will only be triggered if you force individual deletes, i.e. if you pass true as first argument
   *
   * @param Boolean $forceIndividualDeletes
   *
   * @return Integer Number of deleted rows
   */
  public function delete($forceIndividualDeletes = false)
  {
    if($forceIndividualDeletes)
    {
      $objects = $this->getQuery()->delete()->execute();
      $nbDeleted = 0;
      foreach($objects as $object)
      {
        $object->delete();
        $nbDeleted++;
      }
    }
    else
    {
      if (!$this->getQuery()->contains('where'))
      {
        // Empty queries don't return the number of deleted rows
        $this->query->addWhere('1 = 1');
      }
      $nbDeleted = $this->query->delete()->execute();
    }
    $this->cleanup();
    
    return $nbDeleted;
  }
  
  /**
   * Prepares a pager based on the finder
   * The pager is initialized (it knows how many pages it contains)
   * But it won't be populated until you call getResults() on it
   *
   * @param integer $page The current page (1 by default)
   * @param integer $maxPerPage The maximum number of results per page (10 by default)
   *
   * @return sfDoctrineFinderPager The initialized pager object
   */
  public function paginate($page = 1, $maxPerPage = 10)
  {
    throw new Exception('This method is not yet implemented');
  }
  
  /**
   * Updates the records found by the finder
   * Beware that behaviors based on hooks in the object's save() method
   * Will only be triggered if you force individual saves, i.e. if you pass true as second argument
   *
   * @param Array $values Associative array of keys and values to replace
   * @param Boolean $forceIndividualSaves
   *
   * @return Integer Number of deleted rows
   */
  public function set($values, $forceIndividualSaves = false)
  {
    $this->cleanup();
    throw new Exception('This method is not yet implemented');
  }
  
  /**
   * Cleans up the query object (if necessary)
   *
   * @return sfDoctrineFinder the current finder object
   */
  protected function cleanup()
  {
    if($this->reinit)
    {
      $this->initialize($this->connection, $this->class, $this->alias);
    }
    
    return $this;
  }
  
  /**
   * Finalizes the query, executes it and hydrates results
   * 
   * @return array List of Doctrine_Record objects
   */
  public function doFind()
  {
    throw new Exception('This method is not yet implemented');
  }
  
  /**
   * Adds missing Joins from with() and withColumn()
   */
  protected function addMissingJoins()
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }
  
  // Hydrating
  
  /**
   * Ask the finder to hydrate related records
   * Equivalent to Doctrine's JOIN DQL
   * Examples:
   *   // Article has an author, article has a category, and author has a group
   *   $articleFinder->with('Author')->find();
   *   $articleFinder->with('Author', 'Category')->find();
   *   $articleFinder->with('Author', 'Group')->find();
   *   $articleFinder->join('Author')->with('Group')->find();
   *   // By default, the finder uses a simple join (where) but you can force another join
   *   $articleFinder->leftJoin('Author')->with('Author')->find();
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function with($classes)
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }
  
  /**
   * Ask the finder to hydrate related i18n records
   *
   * @param string $culture Optional culture value. If no culture is given, the current user's culture is used
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function withI18n($culture = null)
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }

  /**
   * Ask the finder to hydrate related columns
   * Columns hydrated by way of withColumn() can be retrieved on the object via getColumn()
   * If the join was not explicitly declared earlier in the finder, it guesses it
   * Examples:
   *   $article = $articleFinder->join('Author')->withColumn('Author.Name')->findOne();
   *   // The join() can be omitted, in which case the finder will try to guess the join based on the schema
   *   $article = $articleFinder->withColumn('Author.Name')->findOne();
   *   // Columns added by way of withColumn() can be retrieved by getColumn()
   *   $authorName = $article->getColumn('Author.Name');
   *
   *   // Alias support
   *   $article = $articleFinder->withColumn('Author.Name', 'authorName')->findOne();
   *   $authorName = $article->getColumn('authorName');
   *
   *   // type support
   *   $article = $articleFinder->withColumn('Author.Name', 'authorName', 'string')->findOne();
   *   $authorName = $article->getColumn('authorName');
   *
   *   // Support for calculated columns
   *   $articles = articleFinder->
   *     join('Comment')->
   *     withColumn('COUNT(comment.ID)', 'NbComments')->
   *     groupBy('Article.Id')->
   *     find();
   *
   * @param string $column The column to add. Can be a calculated column
   * @param string $alias Optional alias for column retrieval
   * @param string $type Optional type converter to be sure the retrieved column has the correct type
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function withColumn($column, $alias = null, $type = null)
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }
  
  // Finder Filters
  
  /**
   * Finder Fluid Interface for Doctrine_Query::distinct()
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function distinct()
  {
    $this->query->distinct();
    
    return $this;
  }
  
  /**
   * Finder Fluid Interface for Doctrine_Query::limit()
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function limit($limit)
  {
    $this->query->limit($limit);
    
    return $this;
  }
  
  /**
   * Finder Fluid Interface for Doctrine_Query::offset()
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function offset($offset)
  {
    $this->query->offset($offset);
    
    return $this;
  }
  
  /**
   * Finder Fluid Interface for Doctrine_Query::addWhere()
   * Infers $column, $value, $comparison from $columnName and some optional arguments
   * Examples:
   *   $articleFinder->where('IsPublished')
   *    => $query->addWhere('a.is_published = ?', array(true))
   *   $articleFinder->where('CommentId', 3)
   *    => $query->addwhere('a.comment_id = ?', array(3))
   *   $articleFinder->where('Title', 'like', '%foo')
   *    => $query->addWhere('a.title LIKE ?', array('%foo'))
   *
   * @param      string  $columnName PHPName of the column bearing the condition
   * @param      string  $valueOrOperator  Value if 2 arguments, operator otherwise
   * @param      string  $value  Value if 3 arguments (optional)
   * @param      string  $namedCondition  If condition is to be stored for later combination (see combine())
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function where()
  {
    $arguments = func_get_args();
    $columnName = array_shift($arguments);
    $column = $this->getColName($columnName);
    if(isset($arguments[2]))
    {
      $namedCondition = $arguments[2];
      unset($arguments[2]);
    }
    else
    {
      $namedCondition = null;
    }
    list($value, $comparison) = self::getValueAndComparisonFromArguments($arguments);
    $this->addCondition('and', $column, $comparison, $value, $namedCondition);
    
    return $this;
  }

  /**
   * Infers $column, $value, $comparison from $columnName and some optional arguments
   *
   * @param      string  $columnName PHPName of the column bearing the condition
   * @param      string  $valueOrOperator  Value if 2 arguments, operator otherwise
   * @param      string  $value  Value if 3 arguments (optional)
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function orWhere()
  {
    $arguments = func_get_args();
    $columnName = array_shift($arguments);
    $column = $this->getColName($columnName);
    list($value, $comparison) = self::getValueAndComparisonFromArguments($arguments);
    $this->addCondition('or', $column, $comparison, $value);
    
    return $this;
  }

  protected function addCondition($cond = 'and', $column, $comparison, $value, $namedCondition = null)
  {
    if($comparison == ' NOT IN ')
    {
      if($cond == 'or')
      {
        throw new Exception('sfDoctrineFinder cannot OR a condition with a NOT IN');
      }
      $this->query->whereIn($column, $value, true);
    }
    else if($comparison == ' IN ')
    {
      if($cond == 'or')
      {
        throw new Exception('sfDoctrineFinder cannot OR a condition with an IN');
      }
      $this->query->whereIn($column, $value);
    }
    else
    {
      if($namedCondition)
      {
        if(is_null($value))
        {
          $this->namedPatterns[$namedCondition] = sprintf('%s %s', $column, $comparison);
        }
        else
        {
          $argName = ':param'.$this->argNumber;
          $this->argNumber++;
          $this->namedPatterns[$namedCondition] = sprintf('%s %s %s', $column, $comparison, $argName);
          $this->namedArgs[$namedCondition][$argName] = $value;
        }
      }
      else
      {
        // The operator of the first condition is ignored
        $cond = $this->queryPattern ? $cond : '';
        if(is_null($value))
        {
          $this->queryPattern .= sprintf(' %s %s %s', $cond, $column, $comparison);
        }
        else
        {
          $argName = ':param'.$this->argNumber;
          $this->argNumber++;
          $this->queryPattern .= sprintf(' %s %s %s %s', $cond, $column, $comparison, $argName);
          $this->queryArgs[$argName] = $value;
        }
      }
    }
  }
  
  /**
   * Combine named conditions into the main query or into a new named condition
   * Named conditions are to be defined in where()
   *
   * @param Array $conditions list of named conditions already set by way of where()
   * @param string $operator Combine operator ('and' or 'or')
   * @param string $namedCondition  If combined condition is to be stored for later combination (see combine())
   * 
   * @see where()
   * @return     sfDoctrineFinder the current finder object
   */
  public function combine($conditions, $operator = 'and', $namedCondition = null)
  {
    $addMethod = 'add'.ucfirst(strtolower(trim($operator)));
    if(!is_array($conditions))
    {
      $conditions = array($conditions);
    }
    
    $pattern = '(';
    $args = array();
    $isFirst = true;
    foreach($conditions as $condition)
    {
      if(!$isFirst)
      {
        $pattern .= ' ' . $operator . ' ';
      }
      $pattern .= $this->namedPatterns[$condition];
      $args = array_merge($args, $this->namedArgs[$condition]);
      unset($this->namedPatterns[$condition]);
      unset($this->namedArgs[$condition]);
      $isFirst = false;
    }
    $pattern .= ')';
    
    if($namedCondition)
    {
      $this->namedPatterns[$namedCondition] = $pattern;
      $this->namedArgs[$namedCondition] = $args;
    }
    else
    {
      $cond = $this->queryPattern ? 'and' : '';
      $this->queryPattern .= sprintf(' %s %s', $cond, $pattern);
      $this->queryArgs = array_merge($this->queryArgs, $args);
    }
    
    return $this;
  }
  
  /**
   * Finder fluid method to restrict results to a related object
   * Examples:
   *   $commentFinder->relatedTo($article)
   *
   * @param  Doctrine_Record $object The related object to restrict to
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function relatedTo($object)
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }
  
  /**
   * Finder Fluid Interface for Doctrine_Query::orderby()
   * Infers $column and $order from $columnName and some optional arguments
   * Examples:
   *   $articleFinder->orderBy('CreatedAt')
   *    => $query->orderBy('Article.created_at asc')
   *   $articlefinder->orderBy('CategoryId', 'desc')
   *    => $query->orderBy('Article.category_id desc')
   *
   * @param string $columnName The column to order by
   * @param string $order      The sorting order. 'asc' by default, also accepts 'desc'
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function orderBy($columnName, $order = 'asc')
  {
    $column = $this->getColName($columnName);
    if(!in_array(strtolower($order), array('asc', 'desc')))
    {
      throw new Exception('sfPropelFinder::orderBy() only accepts "asc" or "desc" as argument');
    }
    $this->query->orderby(sprintf('%s %s', $column, strtoupper($order)));
    
    return $this;
  }
  
  /**
   * Finder Fluid Interface for Doctrine_Query::groupBy()
   * Infers $column and $order from $columnName and some optional arguments
   * Examples:
   *   $articleFinder->groupBy('CreatedAt')
   *    => $query->groupBy('Article.created_at')
   *
   * @param string $columnName The column to group by
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function groupBy($columnName)
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }
  
  /**
   * Finder Fluid Interface for Doctrine_Query::groupBy() but this times we add all columns from given class.
   * Examples:
   *   $articleFinder->groupBy('Article')
   *    => $query->groupBy('Article.id')->groupBy('Article.title')->groupBy('Article.created_at')
   * @param string $class
   *
   * @return sfDoctrineFinder the current finder object
   */
  public function groupByClass($class)
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }
  /**
   * Guess sort column based on their names
   * Will look primarily for columns named:
   * 'UpdatedAt', 'UpdatedOn', 'CreatedAt', 'CreatedOn', 'Id'
   * You can change this sequence by modifying the app_sfPropelFinder_sort_column_guesses value
   *
   * @param string $direction 'desc' (default) or 'asc'
   *
   * @return sfDoctrineFinder $this the current finder object
   */
  public function guessOrder($direction = 'desc')
  {
    $columnNames = self::camelize($this->getObject()->getTable()->getColumnNames());
    foreach(sfConfig::get('app_sfPropelFinder_sort_column_guesses', array('UpdatedAt', 'UpdatedOn', 'CreatedAt', 'CreatedOn', 'Id')) as $testColumnName)
    {
      if(in_array($testColumnName, $columnNames))
      {
        $this->orderBy($testColumnName, $direction);
        return $this;
      }
    }
    
    throw new Exception('Unable to figure out the column to use to order rows in sfDoctrineFinder::guessOrder()');
  }
  
  /**
   * Finder Fluid Interface for Doctrine_Query::join()
   * Infers $column1, $column2 and $operator from $relatedClass and some optional arguments
   * Uses table introspection to guess the related columns
   * Examples:
   *   $articleFinder->join('Comment')
   *   $articleFinder->join('Category', 'RIGHT JOIN')
   *   $articleFinder->join('Article.CategoryId', 'Category.Id', 'RIGHT JOIN')
   * 
   * @param  string $classOrColumn Class to join if 1 argument, first column of the join otherwise
   * @param  string $column Second column of the join if more than 1 argument
   * @param  string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
   *
   * @return     sfDoctrineFinder the current finder object
   */
  public function join()
  {
    throw new Exception('This method is not yet implemented');
    
    return $this;
  }
  
  /**
   * Do no reinitialize the finder object after a termination method
   * By default the Doctrine_Query is wiped off whenever a termination method is called
   * Calling this method with true as parameter will keep the internal Query intact for the next termination method
   *
   * @param  Boolean $keep true (default) or false
   *
   * @return sfDoctrineFinder the current finder object
   */
  public function keepQuery($keep = true)
  {
    $this->reinit = $keep;
    
    return $this;
  }
  
  protected function hasRelation($peerName)
  {
    throw new Exception('This method is not yet implemented');
  }
  
  /**
   * Guess the relation to another class
   *
   * @param string $phpName Doctrine_Record Class name (e.g. 'Article')
   * 
   * @return array A list with the two columns member of the relationship
   */
  public function getRelation($phpName)
  {
    throw new Exception('This method is not yet implemented');
  }
  
  /**
   * Finds a relation between two classes by introspection
   */
  protected function findRelation($phpName, $peerClass)
  {
    throw new Exception('This method is not yet implemented');
  }
  
  /**
   * Behavior-like supplementary getter for supplementary columns added by way of withColumn()
   *
   * @param Doctrine_Record $object Doctrine model object
   * @param string $alias Supplementary column name
   *
   * @return mixed The value of the column set by setColumn()
   */
  public function getColumn($object, $alias)
  {
    throw new Exception('This method is not yet implemented');
  }
  
  /**
   * Behavior-like supplementary setter for supplementary columns added by way of withColumn()
   *
   * @param Doctrine_Record $object Doctrine model object
   * @param string $alias Supplementary column name
   * @param mixed The value of the column
   */
  public function setColumn($object, $alias, $value)
  {
    throw new Exception('This method is not yet implemented');
  }
  
  protected function getColName($phpName, $class = null, $autoAddJoin = true)
  {
    if(strpos($phpName, '.') !== false)
    {
      // Table.Column
      list($class, $phpName) = explode('.', $phpName);
    }
    else if(strpos($phpName, '_') !== false)
    {
      // Table_Column, or Table_Name_Column, so explode is not a solution here
      $limit = strrpos($phpName, '_');
      $class = substr($phpName, 0, $limit);
      $phpName = substr($phpName, $limit + 1);
    }
    else
    {
      // Column
      if(!$class)
      {
        // TODO: guess class
        $class = $this->getClass();
      }
    }
    if(!array_key_exists($class, $this->relations))
    {
      $relations = array_flip($this->relations);
      $class = $relations[$class];
    }
    
    return $class . '.' . self::underscore($phpName);
  }
  
  public function __call($name, $arguments)
  {
    if(strpos($name, 'where') === 0)
    {
      array_unshift($arguments, substr($name, 5));
      return call_user_func_array(array($this, 'where'), $arguments);
    }
    if(strpos($name, 'orWhere') === 0)
    {
      array_unshift($arguments, substr($name, 7));
      return call_user_func_array(array($this, 'orWhere'), $arguments);
    }
    if(strpos($name, 'orderBy') === 0)
    {
      return $this->orderBy(substr($name, 7), $arguments);
    }
    if(strpos($name, 'join') === 0)
    {
      return $this->join(substr($name, 4), $arguments);
    }
    if(($pos = strpos($name, 'Join')) > 0)
    {
      $joinType = strtoupper(substr($name, 0, $pos)) . ' JOIN';
      array_push($arguments, $joinType);
      return call_user_func_array(array($this, 'join'), $arguments);
    }
    if(strpos($name, 'findBy') === 0)
    {
      array_unshift($arguments, substr($name, 6));
      return call_user_func_array(array($this, 'findBy'), $arguments);
    }
    if(strpos($name, 'findOneBy') === 0)
    {
      array_unshift($arguments, substr($name, 9));
      return call_user_func_array(array($this, 'findOneBy'), $arguments);
    }
    throw new Exception(sprintf('Undefined method sfDoctrineFinder::%s()', $name));
  }
}