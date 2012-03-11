<?php

class DbFinderAdminGenerator extends sfGenerator
{
  const
    PROPEL     = 'Propel',
    DOCTRINE   = 'Doctrine';
  protected
    $generator = null,
    $orm       = null;
  
  /**
   * Initializes the current sfGenerator instance.
   *
   * @param sfGeneratorManager A sfGeneratorManager instance
   */
  public function initialize($generatorManager)
  {
    $this->generatorManager = $generatorManager;
  }
  
  /**
   * Generates classes and templates in cache.
   *
   * @param array The parameters
   *
   * @return string The data to put in configuration cache
   */
  public function generate($params = array())
  {
    if (!isset($params['model_class']))
    {
      $error = 'You must specify a "model_class"';
      $error = sprintf($error, $entry);

      throw new sfParseException($error);
    }
    $modelClass = $params['model_class'];

    if (!class_exists($modelClass))
    {
      $error = 'Unable to scaffold unexistant model "%s"';
      $error = sprintf($error, $modelClass);

      throw new sfInitializationException($error);
    }
    
    $tmp = new $modelClass;
    if($tmp instanceof BaseObject)
    {
      $this->generator = new sfPropelAdminGenerator();
      $this->orm = self::PROPEL;
    }
    elseif($tmp instanceof Doctrine_Record)
    {
      $this->generator = new sfDoctrineAdminGenerator();
      $this->orm = self::DOCTRINE;
    }
    $this->generator->initialize($this->generatorManager);
    $this->generator->setGeneratorClass('DbFinderAdmin');
    $this->generator->DbFinderAdminGenerator = $this;
    return $this->generator->generate($params);
  }
  
  public function getColumnType($column)
  {
    if($this->orm == self::PROPEL)
    {
      $type = $column->getCreoleType();
    }
    else
    {
      $type = $column->getDoctrineType();
    }
    switch($type)
    {
      case CreoleTypes::DATE:
      case 'date':
        $type = 'date';
        break;
      case CreoleTypes::TIMESTAMP:
      case 'timestamp':
        $type = 'timestamp';
        break;
      case CreoleTypes::CHAR:
      case 'char':
      case CreoleTypes::VARCHAR:
      case 'string':
      case CreoleTypes::LONGVARCHAR:
      case 'clob':
        $type = 'string';
        break;
    }
    
    return $type;
  }
  
  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->generator, $method), $arguments);
  }
}