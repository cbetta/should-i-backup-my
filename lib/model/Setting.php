<?php

class Setting extends BaseSetting
{
  public static function get($domain, $key)
  {
    return sfPropelFinder::from('Setting')->where('Domain', $domain)->where('Key', $key)->findOne();
  }
  
  public static function getAll($domain, $key = null)
  {
    if ($key) return sfPropelFinder::from('Setting')->where('Domain', $domain)->where('Key', $key)->find();
    else return sfPropelFinder::from('Setting')->where('Domain', $domain)->find();
  }
  
  public static function exists($domain, $key, $value) {
    $setting =  sfPropelFinder::from('Setting')->where('Domain', $domain)->where('Key', $key)->where('Value', $value)->findOne();
    return $setting != null;;
  }
  
  public static function getByDomain($domain)
  {
    $models = Setting::getAll($domain);
    $settings = array();
    foreach ($models as $model) {
      $settings[$model->getKey()] = $model->getValue(); 
    }
    return $settings;
  }
}
