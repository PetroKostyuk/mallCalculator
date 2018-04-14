<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 14-Apr-18
 * Time: 21:57
 */

namespace App\Model;

interface IFormRenderSetup{

    public function setupFormRenderer(\Nette\Application\UI\Form $form) : void;

}