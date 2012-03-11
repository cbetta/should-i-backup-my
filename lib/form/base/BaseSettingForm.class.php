<?php

/**
 * Setting form base class.
 *
 * @package    hackday
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfPropelFormGeneratedTemplate.php 16976 2009-04-04 12:47:44Z fabien $
 */
class BaseSettingForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'     => new sfWidgetFormInputHidden(),
      'domain' => new sfWidgetFormInput(),
      'key'    => new sfWidgetFormInput(),
      'value'  => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'     => new sfValidatorPropelChoice(array('model' => 'Setting', 'column' => 'id', 'required' => false)),
      'domain' => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'key'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'value'  => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('setting[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Setting';
  }


}
