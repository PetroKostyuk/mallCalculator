<?php
/**
 * Created by PhpStorm.
 * User: petro
 * Date: 13-Apr-18
 * Time: 21:41
 */

namespace App\Model\Expression;

interface IExpression{

    public function getValue() : int;

    public function toString() : string;

}
