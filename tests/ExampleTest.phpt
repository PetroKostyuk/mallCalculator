<?php

namespace Test;

use App\Model\ExpressionParseException;
use App\Model\ExpressionParser;
use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';


class ExampleTest extends Tester\TestCase
{

    /** @var Nette\DI\Container */
	private $container;

	/** @var ExpressionParser */
	private $expressionParser;


	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
        $this->expressionParser = $this->container->getByType(ExpressionParser::class);
	}

	public function paramsParsingExceptions(){
	    return [
            ['12 + x', ExpressionParseException::PARSE_UNSUPPORTED_SYMBOL],
            ['12x', ExpressionParseException::PARSE_UNSUPPORTED_SYMBOL],

            ['10 5', ExpressionParseException::PARSE_WHITESPACE_NUMBER],

            ['1 (4 - 5)', ExpressionParseException::COMPOSE_LEFT_PARENTHESIS_NO_OPERATION],
            ['1 + 4(1)', ExpressionParseException::COMPOSE_LEFT_PARENTHESIS_NO_OPERATION],

            ['(1+1) 2', ExpressionParseException::COMPOSE_RIGHT_PARENTHESIS_NO_OPERATION],
            ['(1+1)2', ExpressionParseException::COMPOSE_RIGHT_PARENTHESIS_NO_OPERATION],

            ['1 + (1 + 1', ExpressionParseException::COMPOSE_LEFT_PARENTHESIS_NO_MATCH],
            ['1 + (1 - 1) + (1', ExpressionParseException::COMPOSE_LEFT_PARENTHESIS_NO_MATCH],
            ['1 + (1 - 1) + (', ExpressionParseException::COMPOSE_LEFT_PARENTHESIS_NO_MATCH],

            ['1 + 1) + 1', ExpressionParseException::COMPOSE_RIGHT_PARENTHESIS_NO_MATCH],
            ['(1 + 1) + 4) + 1', ExpressionParseException::COMPOSE_RIGHT_PARENTHESIS_NO_MATCH],


            ['2 +', ExpressionParseException::COMPOSE_OPERATION_ON_END],
            ['2 + 3 -', ExpressionParseException::COMPOSE_OPERATION_ON_END],
            ['2 + (3 - 4) +', ExpressionParseException::COMPOSE_OPERATION_ON_END],

            ['', ExpressionParseException::COMPOSE_EMPTY_EXPRESSION],
            ['    ', ExpressionParseException::COMPOSE_EMPTY_EXPRESSION],

            ['()', ExpressionParseException::COMPOSE_EMPTY_PARENTHESIS],
            ['4+() -2', ExpressionParseException::COMPOSE_EMPTY_PARENTHESIS],
            ['1 + ((2 - ()) + 5)', ExpressionParseException::COMPOSE_EMPTY_PARENTHESIS],

        ];
    }

    /**
     * Test non-valid expressions and compares thrown exception code to expected exception code
     * @dataProvider paramsParsingExceptions
     * @param $expressionString string expression string to be parsed that should contain error
     * @param $exceptionCode int code of expected error
     */
    public function testParsingExceptions($expressionString, $exceptionCode)
    {
        Assert::exception(function() use($expressionString){
            $this->expressionParser->parseExpressionString($expressionString);
        }, ExpressionParseException::class, null, $exceptionCode);
    }


    public function paramsCalculationCorrectness()
    {
        return [
            ['Single number', '4', 4],
            ['Positive number', '+4', 4],
            ['Negative number', '-4', -4],
            ['Multiple operations', '1+2-3+4-5', -1],
            ['Whitespaces', "\n 1 + 2  \t  - 3+4  \n  -5", -1],
            ['Parenthesis', '2 + (3 + 4)', 9],
            ['Negative parenthesis', '2 -(3 + 4)', -5],
            ['Multiple parenthesis', '(1 + 2) - (3 + 4)', -4],
            ['Nested parenthesis', '(1 + (2 + 4 - ((1))))', 6],
        ];
    }

    /** @dataProvider paramsCalculationCorrectness */
    public function testCalculationCorrectness($testcase, $expressionString, $expectedValue)
    {
        $expression = $this->expressionParser->parseExpressionString($expressionString);
        $value = $expression->getValue();

        Assert::same($expectedValue, $value, 'Expected value of expression differs from calculated value in testcase: '.$testcase);
    }
}


$test = new ExampleTest($container);
$test->run();
