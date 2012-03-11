<?php
// auto-generated by sfViewConfigHandler
// date: 2009/05/16 15:05:32
$response = $this->context->getResponse();


  $templateName = sfConfig::get('symfony.view.'.$this->moduleName.'_'.$this->actionName.'_template', $this->actionName);
  $this->setTemplate($templateName.$this->viewName.$this->getExtension());



  if (!is_null($layout = sfConfig::get('symfony.view.'.$this->moduleName.'_'.$this->actionName.'_layout')))
  {
    $this->setDecoratorTemplate(false === $layout ? false : $layout.$this->getExtension());
  }
  else if (is_null($this->getDecoratorTemplate()) && !$this->context->getRequest()->isXmlHttpRequest())
  {
    $this->setDecoratorTemplate('' == 'layout' ? false : 'layout'.$this->getExtension());
  }
  $response->addHttpMeta('content-type', 'text/html', false);
  $response->addMeta('title', 'Should I Backup My...', false, false);
  $response->addMeta('description', 'Tells you what you probably already knew', false, false);
  $response->addMeta('keywords', 'symfony, project, backup, hackday, openhack, flickr, yahoo, backups,', false, false);
  $response->addMeta('language', 'en', false, false);
  $response->addMeta('robots', 'index, follow', false, false);

  $response->addStylesheet('main.css', '', array ());
  $response->addJavascript('jquery-1.3.2.min.js', '', array ());
  $response->addJavascript('search.js', '', array ());


