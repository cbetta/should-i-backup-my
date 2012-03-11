<?php

/**
 * search actions.
 *
 * @package    hackday
 * @subpackage search
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class searchActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
  }
  
  /**
   * Redirects a search to the info page
   */
   public function executeTarget(sfWebRequest $request) 
   {
     // get the search query
     $target = $request->getParameter('search');
     try {
       $this->redirect('@answer?target='.$target);
     } catch(Exception $e) {
       var_dump($e);
       exit(0);
       $this->redirect('@homepage');
     }
   }
}
