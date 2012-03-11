<?php

/*
 * This file is part of the sfPropelFinder package.
 * 
 * (c) 2007 François Zaninotto <francois.zaninotto@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
class sfPropelFinderUtils
{
  public static function relateObjects($new, $existingObjects, $isNew)
  {
    // brute force (to be optimized later)
    foreach ($existingObjects as $existingObject)
    {
      $methodName = 'add'.get_class($existingObject);
      if(method_exists($new, $methodName))
      {
        if($isNew)
        {
          call_user_func(array($new, 'init'.get_class($existingObject).'s'));
        }
        call_user_func(array($new, $methodName), $existingObject);
        break;
      }
    }
  }
  
  public static function relateI18nObjects($new, $existingObjects, $culture)
  {
    // brute force (to be optimized later)
    foreach ($existingObjects as $existingObject)
    {
      $methodName = 'set'.get_class($new).'ForCulture';
      if(method_exists($existingObject, $methodName))
      {
        call_user_func(array($existingObject, $methodName), $new, $culture);
        call_user_func(array($new, 'set'.get_class($existingObject)), $existingObject);
        break;
      }
    }
  }
  
  public static function getPeerClassFromClass($class)
  {
    $tmp = new $class();
    $class = get_class($tmp->getPeer());
    return $class;
  }

  public static function getClassFromPeerClass($peerClass)
  {
    $omClass = call_user_func(array($peerClass, 'getOMClass'));
    return substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
  }
  
  public static function getColumnsForPeerClass($peerClass)
  {
    if(class_exists($peerClass))
    {
      $tableMap = call_user_func(array($peerClass, 'getTableMap'));
      return $tableMap->getColumns();
    }
    return false;
  }
  
  
}