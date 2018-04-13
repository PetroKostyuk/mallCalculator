<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class ExpressionFormFactory
{
	use Nette\SmartObject;

	/**
	 * @return Form
	 */
	public function create()
	{
        $form = new Form();

        $form->addTextArea('expression', 'Expression');
        $form->addSubmit('submit', 'Submit');

		return $form;
	}
}
