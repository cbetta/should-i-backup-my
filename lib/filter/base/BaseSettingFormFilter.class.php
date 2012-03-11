<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * Setting filter form base class.
 *
 * @package    hackday
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormFilterGeneratedTemplate.php 16976 2009-04-04 12:47:44Z fabien $
 */
class BaseSettingFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'domain' => new sfWidgetFormFilterInput(),
      'key'    => new sfWidgetFormFilterInput(),
      'value'  => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'domain' => new sfValidatorPass(array('required' => false)),
      'key'    => new sfValidatorPass(array('required' => false)),
      'value'  => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('setting_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Setting';
  }

  public function getFields()
  {
    return array(
      'id'     => 'Number',
      'domain' => 'Text',
      'key'    => 'Text',
      'value'  => 'Text',
    );
  }
}
