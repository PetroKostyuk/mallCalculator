<?php

namespace App\Forms;

use App\Model\FormRenderSetupProvider;
use App\Model\IFormRenderSetup;
use Nette;
use Nette\Application\UI\Form;


class ExpressionFormFactory
{
	use Nette\SmartObject;

	/** @var IFormRenderSetup */
	protected $formRenderSetup;

	public function __construct(FormRenderSetupProvider $formRenderSetupProvider)
    {
        $this->formRenderSetup = $formRenderSetupProvider->get();
    }

    /**
	 * @return Form
	 */
	public function create()
	{
        $form = new Form();

        $form->addTextArea('expression', 'Enter your expression');
        $form->addSubmit('submit', 'Get Result!');

        $this->formRenderSetup->setupFormRenderer($form);

		return $form;
	}
}
