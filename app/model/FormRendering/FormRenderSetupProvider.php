<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 14-Apr-18
 * Time: 22:10
 */

namespace App\Model;

class FormRenderSetupProvider{

    /** @var IFormRenderSetup */
    private $formRenderSetup;

    public function __construct(FormRenderSetupMaterialize $formRenderSetupMaterialize)
    {
        $this->formRenderSetup = $formRenderSetupMaterialize;
    }

    public function get() : IFormRenderSetup
    {
        return $this->formRenderSetup;
    }
}