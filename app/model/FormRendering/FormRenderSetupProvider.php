<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 14-Apr-18
 * Time: 22:10
 */

namespace App\Model;

class FormRenderSetupProvider{

    /** @var \App\Model\IFormRenderSetup */
    private $formRenderSetup;

    public function __construct(\App\Model\FormRenderSetupMaterialize $formRenderSetupMaterialize)
    {
        $this->formRenderSetup = $formRenderSetupMaterialize;
    }

    public function get() : \App\Model\IFormRenderSetup
    {
        return $this->formRenderSetup;
    }
}