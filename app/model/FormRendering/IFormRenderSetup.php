<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 14-Apr-18
 * Time: 21:57
 */

namespace App\Model;

use \Nette\Application\UI\Form;

interface IFormRenderSetup{

    public function setupFormRenderer(Form $form) : void;

}