<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 13-Apr-18
 * Time: 21:41
 */

namespace App\Model\Expression;

class ExpressionArray implements IExpression{

    /** @var IExpression[] */
    private $expressions = [];

    public function addExpression(IExpression $expression){
        $this->expressions[] = $expression;
    }

    function getValue() : int
    {
        $value = 0;

        foreach ($this->expressions as $expression){
            $value += $expression->getValue();
        }

        return $value;
    }

    function toString() : string
    {
        $str = '(';
        if(sizeof($this->expressions)>0)$str .= $this->expressions[0]->toString();

        for($i=1; $i<sizeof($this->expressions); $i++){
            $str .= ' + ' . $this->expressions[$i]->toString();
        }

        $str .= ')';
        return $str;
    }
}
