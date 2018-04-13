<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 13-Apr-18
 * Time: 21:41
 */

namespace App\Model\Expression;

class ExpressionValue implements IExpression{

    /** @var int  */
    private $value;

    public function __construct($expressionString)
    {
        $this->value = intval($expressionString);
    }

    function getValue(): int
    {
        return $this->value;
    }

    function toString() : string
    {
        return '' . $this->value;
    }
}
