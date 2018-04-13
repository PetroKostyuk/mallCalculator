<?php

namespace App\Model;

use App\Model\Expression\ExpressionArray;
use App\Model\Expression\ExpressionNegative;
use App\Model\Expression\ExpressionValue;
use App\Model\Expression\IExpression;
use Nette;


/**
 * Users management.
 */
class ExpressionParser
{
	use Nette\SmartObject;

    /**
     * Takes arbitrary string, parses it and converts to instance of IExpression.
     * @param $expressionString
     * @return IExpression
     * @throws ExpressionParseException
     */
    public function parseExpressionString($expressionString) : IExpression
    {
        $expressionStringParts = $this->parseExpressionStringToParts($expressionString);
        $expression = $this->expressionPartsToExpression($expressionStringParts);

        return $expression;
    }

    private function charIsDigit($char) : bool
    {
        return $char >= '0' && $char <= '9';
    }

    private function charIsOperation($char) : bool
    {
        return in_array($char, ['+', '-', '(', ')']);
    }

    /**
     * Converts arbitrary string into array of functional parts of expression. Each functional part is either
     * operator or operand. Several digits long operators are grouped together to one functional part. All
     * non functional symbols (as whitespaces) are ignored.
     * @param string $expressionString
     * @return array functional parts, eg: ['14', '-', '(', '5', ... ]
     */
    private function parseExpressionStringToParts(string $expressionString) : array
    {
        $expressionParts = [];

        $bufferIsDigit = false;
        $buffer = '';

        for($i=0; $i<strlen($expressionString); $i++){
            $char = $expressionString[$i];

            $charIsDigit = $this->charIsDigit($char);
            $charIsOperation = $this->charIsOperation($char);

            if($charIsDigit){
                if($bufferIsDigit){
                    $buffer .= $char;
                }else{
                    $buffer = $char;
                }
                $bufferIsDigit = true;
            }

            if($charIsOperation){
                if(!empty($buffer)){
                    $expressionParts[] = $buffer;
                }
                $expressionParts[] = $char;
                $buffer = '';

                $bufferIsDigit = false;
            }
        }

        if(!empty($buffer)){
            $expressionParts[] = $buffer;
        }

        return $expressionParts;
    }

    /**
     * Converts expression parts into instance of IExpression
     * @param array $expressionParts
     * @return IExpression
     * @throws ExpressionParseException
     */
    private function expressionPartsToExpression(array $expressionParts) : IExpression
    {
        $expression = new ExpressionArray();

        $isNegative = false;
        $parenthesisDepth = 0;
        $subexpressionParts = [];

        foreach ($expressionParts as $part){
            if($parenthesisDepth == 0){
                switch ($part){
                    case '-': {
                        $isNegative = true;
                        break;
                    }
                    case '+': {
                        // nothing to do here, we just don't want code to fall to default here
                        break;
                    }
                    case '(': {
                        $parenthesisDepth++;
                        $subexpressionParts = [];
                        break;
                    }
                    case ')': {
                        throw new ExpressionParseException("Right parenthesis without matching left parenthesis");
                        break;
                    }
                    default:{
                        // create expression out of string part
                        $simpleExpression = new ExpressionValue($part);

                        // use negative decorator in case minus precedes expression
                        if($isNegative){
                            $simpleExpression = new ExpressionNegative($simpleExpression);
                        }

                        // add to expression array
                        $expression->addExpression($simpleExpression);
                        break;
                    }
                }
            }else{

                if($part === '(') $parenthesisDepth++;
                if($part === ')') $parenthesisDepth--;

                if($parenthesisDepth > 0){
                    $subexpressionParts[] = $part;
                }else{
                    $subexpression = $this->expressionPartsToExpression($subexpressionParts);

                    // use negative decorator in case minus precedes expression
                    if($isNegative){
                        $subexpression = new ExpressionNegative($subexpression);
                    }

                    // add to expression array
                    $expression->addExpression($subexpression);
                }

            }
        }

        if($parenthesisDepth > 0){
            throw new ExpressionParseException("Left parenthesis without matching right parenthesis");
        }

        return $expression;
    }
}

class ExpressionParseException extends \Exception { }