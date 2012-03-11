<?php

/**
 * admin actions.
 *
 * @package    hackday
 * @subpackage admin
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class adminActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $settings = sfPropelFinder::from('Setting')->find();
    $this->setVar('settings', $settings);
  }
  
  public function executeSave(sfWebRequest $request)
  {
    $setting = new Setting();
    $setting->setDomain($request->getParameter('domain'));
    $setting->setKey($request->getParameter('key'));
    $setting->setValue($request->getParameter('value'));
    $setting->save();
    $this->redirect('@admin');
  }
  
  public function executeDelete(sfWebRequest $request)
  {
    $setting = sfPropelFinder::from('Setting')->where('Id', $request->getParameter('id'))->findOne();
    $setting->delete();
    $this->redirect('@admin');
  }
}
