<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 14-Apr-18
 * Time: 21:59
 */

namespace App\Model;

class FormRenderSetupMaterialize implements IFormRenderSetup{

    public function setupFormRenderer(\Nette\Application\UI\Form $form): void
    {
        $renderer = $form->getRenderer();

        $renderer->wrappers['controls']['container'] = 'div class="row"';
        $renderer->wrappers['pair']['container'] = 'div class="input-field col s12"';

        foreach ($form->getControls() as $control){
            $type = $control->getOption('type');

            if ($type === 'button') {
                $control->getControlPrototype()->addClass('btn');
            }
            if ($type === 'textarea') {
                $control->getControlPrototype()->addClass('materialize-textarea');
            }
        }
    }
}
