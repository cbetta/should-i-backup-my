<?php

/**
 * answer actions.
 *
 * @package    hackday
 * @subpackage answer
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class answerActions extends sfActions
{
 /**
  * Shows the pages with info on the term searched for
  *
  * @param sfRequest $request A request object
  */
  public function executeShow(sfWebRequest $request)
  {   
    $target = $request->getParameter('target');
    // try and see if there is any custom data for this page
      if (Setting::exists('app', 'custom', $target)) {
        $settings = Setting::getByDomain($target);
        
        #determine the state
        if ($settings['state']) $this->setVar('state', $settings['state']);
        else $this->setVar('state', 'Probably');
        
        #determien the font color
        if (@$settings['color']) $this->setVar('color', $settings['color']);
        else $this->setVar('color', '#888');
        
        $settings['news_links'] = Analyse::news($target); 
        $settings['backup_links'] = Analyse::backup($target); 

        
        $this->setVar('settings', $settings);
        
      } else {
        $answer = Analyse::token($target);
        $this->setVar('settings', $answer);
        $this->setVar('state', $answer['state']);
        $this->setVar('color', '#888');
      }
  }
}
