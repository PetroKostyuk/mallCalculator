<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 13-Apr-18
 * Time: 21:41
 */

namespace App\Model\Expression;

class ExpressionNegative implements IExpression{

    /** @var IExpression */
    private $expression;

    public function __construct(IExpression $expression)
    {
        $this->expression = $expression;
    }

    public function getValue() : int
    {
        return (-1 * $this->expression->getValue());
    }

    function toString() : string
    {
        return ' -(' . $this->expression->toString() . ')';
    }
}
