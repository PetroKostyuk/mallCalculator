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

    const PART_NONE = 0;
    const PART_NUMBER = 1;
    const PART_OPERATION = 2;
    const PART_LEFT_PARENTHESIS = 3;
    const PART_RIGHT_PARENTHESIS = 4;

    /**
     * Takes arbitrary string, parses it and converts to instance of IExpression.
     * @param $expressionString
     * @return IExpression
     * @throws ExpressionParseException
     */
    public function parseExpressionString($expressionString) : IExpression
    {
        $expressionStringParts = $this->parseExpressionStringToParts($expressionString);
        $expression = $this->composeExpressionPartsToExpression($expressionStringParts);

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

    private function charIsAllowedWhitespace($char) : bool
    {
        return in_array($char, [' ', "\n", "\t"]);
    }

    /**
     * Converts arbitrary string into array of functional parts of expression. Each functional part is either
     * operator or operand. Several digits long operators are grouped together to one functional part. All
     * non functional symbols (as whitespaces) are ignored.
     * @param string $expressionString
     * @return array functional parts, eg: ['14', '-', '(', '5', ... ]
     * @throws ExpressionParseException
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
            $charIsAllowedWhitespace = $this->charIsAllowedWhitespace($char);

            // char is not allowed
            if(!($charIsDigit || $charIsOperation || $charIsAllowedWhitespace)){
                throw ExpressionParseException::buildFromString("Expression contains symbol, that is not allowed",
                    ExpressionParseException::PARSE_UNSUPPORTED_SYMBOL, $expressionString, $i);
            }

            // there is whitespace between numbers without operation. Condition means:
            // (we are reading number AND we have had number before current number AND there is whitespace between them
            // PS: $expressionString[$i-1] can't be out of bounds since buffer is not empty ~ we had something before
            if($charIsDigit && strlen($buffer)>0 && $this->charIsAllowedWhitespace($expressionString[$i-1])){
                throw ExpressionParseException::buildFromString("Expression contains numbers, that follows previous number without operator in between",
                    ExpressionParseException::PARSE_WHITESPACE_NUMBER, $expressionString, $i);
            }

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
    private function composeExpressionPartsToExpression(array $expressionParts) : IExpression
    {
        $expression = new ExpressionArray();

        $isNegative = false;
        $parenthesisDepth = 0;
        $subexpressionParts = [];

        $lastLeftParenthesisIndex = 0;
        $lastPartType = self::PART_NONE;

        for ($i=0; $i<counT($expressionParts); $i++){
            $part = $expressionParts[$i];
            if($parenthesisDepth == 0){
                switch ($part){
                    case '-': {
                        $isNegative = true;
                        $lastPartType = self::PART_OPERATION;
                        break;
                    }
                    case '+': {
                        $lastPartType = self::PART_OPERATION;
                        break;
                    }
                    case '(': {
                        if($lastPartType === self::PART_NUMBER){
                            throw ExpressionParseException::buildFromParts("There is no operation between number and left parenthesis",
                                ExpressionParseException::COMPOSE_LEFT_PARENTHESIS_NO_OPERATION, $expressionParts, $i);
                        }
                        $parenthesisDepth++;
                        $subexpressionParts = [];
                        $lastLeftParenthesisIndex = $i;
                        $lastPartType = self::PART_LEFT_PARENTHESIS;
                        break;
                    }
                    case ')': {
                        throw ExpressionParseException::buildFromParts("Right parenthesis without matching left parenthesis",
                            ExpressionParseException::COMPOSE_RIGHT_PARENTHESIS_NO_MATCH, $expressionParts, $i);
                        break;
                    }
                    default:{
                        if($lastPartType === self::PART_RIGHT_PARENTHESIS){
                            throw ExpressionParseException::buildFromParts("There is no operation between right parenthesis and number",
                                ExpressionParseException::COMPOSE_RIGHT_PARENTHESIS_NO_OPERATION, $expressionParts, $i);
                        }

                        // create expression out of string part
                        $simpleExpression = new ExpressionValue($part);

                        // use negative decorator in case minus precedes expression
                        if($isNegative){
                            $simpleExpression = new ExpressionNegative($simpleExpression);
                        }

                        // add to expression array
                        $expression->addExpression($simpleExpression);

                        $lastPartType = self::PART_NUMBER;
                        break;
                    }
                }
            }else{

                if($part === '(') $parenthesisDepth++;
                if($part === ')') $parenthesisDepth--;

                if($parenthesisDepth > 0){
                    $subexpressionParts[] = $part;
                }else{
                    $subexpression = $this->composeExpressionPartsToExpression($subexpressionParts);

                    // use negative decorator in case minus precedes expression
                    if($isNegative){
                        $subexpression = new ExpressionNegative($subexpression);
                    }

                    // add to expression array
                    $expression->addExpression($subexpression);

                    $lastPartType = self::PART_RIGHT_PARENTHESIS;
                }

            }
        }

        if($parenthesisDepth > 0){
            throw ExpressionParseException::buildFromParts("Left parenthesis without matching right parenthesis",
                ExpressionParseException::COMPOSE_LEFT_PARENTHESIS_NO_MATCH, $expressionParts, $lastLeftParenthesisIndex);
        }

        if($lastPartType === self::PART_OPERATION){
            throw ExpressionParseException::buildFromParts("Expression ends with operator without number following",
                ExpressionParseException::COMPOSE_OPERATION_ON_END, $expressionParts, count($expressionParts)-1);
        }

        return $expression;
    }

}


class ExpressionParseException extends \Exception {

    const PARSE_UNSUPPORTED_SYMBOL = 1;
    const PARSE_WHITESPACE_NUMBER = 2;

    const COMPOSE_LEFT_PARENTHESIS_NO_OPERATION = 3;
    const COMPOSE_RIGHT_PARENTHESIS_NO_OPERATION = 4;
    const COMPOSE_LEFT_PARENTHESIS_NO_MATCH = 5;
    const COMPOSE_RIGHT_PARENTHESIS_NO_MATCH = 6;
    const COMPOSE_OPERATION_ON_END = 7;

    /**
     * Creates ExpressionParseException instance with highlighted place of error
     * @param string $errorMessage Error message to be displayed
     * @param string $expressionString String containing whole expression
     * @param int $errorIndex index of error in $expressionString
     * @return ExpressionParseException
     */
    static public function buildFromString(string $errorMessage, int $code, string $expressionString, int $errorIndex){
        $message = $errorMessage . ': ';

        // string before error index
        if($errorIndex > 0){
            $message .= substr($expressionString, 0, $errorIndex);
        }

        // error
        $message .= ' *' . $expressionString[$errorIndex] . '* ';

        // string after error
        if(strlen($expressionString) > $errorIndex+1){
            $message .= substr($expressionString, $errorIndex+1);
        }

        return new ExpressionParseException($message, $code);
    }

    /**
     * Creates ExpressionParseException instance with highlighted place of error
     * @param string $errorMessage  message to be displayed
     * @param array $parts array of string parts after parsing
     * @param int $errorIndex index of part containing error
     * @return ExpressionParseException
     */
    static public function buildFromParts(string $errorMessage, int $code, array $parts, int $errorIndex){
        $message = $errorMessage . ': ';

        for ($i=0; $i<sizeof($parts); $i++){
            if($i === $errorIndex){
                $message .= ' *' . $parts[$i] . '*';
            }else{
                $message .= ' ' . $parts[$i];
            }
        }

        return new ExpressionParseException($message, $code);
    }

}