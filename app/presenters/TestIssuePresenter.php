<?php

namespace App\Presenters;


use App\Forms\ExpressionFormFactory;
use App\Model\ExpressionParseException;
use App\Model\ExpressionParser;
use Nette\Application\UI\Form;

class TestIssuePresenter extends BasePresenter
{
    /** @var  ExpressionFormFactory */
    private $expressionFormFactory;

    /** @var  ExpressionParser */
    private $expressionParser;

    public function __construct(ExpressionFormFactory $expressionFormFactory, ExpressionParser $expressionParser)
    {
        $this->expressionFormFactory = $expressionFormFactory;
        $this->expressionParser = $expressionParser;
    }

    public function renderDefault(string $value = '')
    {
        if(!empty($value)){
            $this->displayExpressionStringResult($value);
        }

		$this->template->anyVariable = 'any value';
	}

	function createComponentExpressionForm(): Form
    {
        $form = $this->expressionFormFactory->create();
        $form->onSuccess[] = [$this, 'processExpressionForm'];

        return $form;
    }

    public function processExpressionForm(Form $form)
    {
        $values = $form->getValues();
        $expressionString = $values->expression;
        $this->displayExpressionStringResult($expressionString);

        $this->redirect('Default');
    }

    private function displayExpressionStringResult(string $expressionString){
        try{
            $expression = $this->expressionParser->parseExpressionString($expressionString);
        }catch (ExpressionParseException $exception){
            $this->flashMessage('There was an error while parsing your expression: ' . $exception->getMessage());
            return;
        }

        $value = $expression->getValue();
        $this->flashMessage($value);
    }


}
