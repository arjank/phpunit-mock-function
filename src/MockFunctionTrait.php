<?php

namespace Potherca\Phpunit\MockFunction;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_RuntimeException;

trait MockFunctionTrait
{
    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @param string $functionName
     * @param array $parameters
     * @param mixed $returnValue
     * @param array $asserts
     *
     * @return Builder
     *
     * @throws PHPUnit_Framework_MockObject_RuntimeException
     */
    final public function getMockFunctionBuilder($functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        if ($this instanceof TestCase === false) {
            throw new PHPUnit_Framework_MockObject_RuntimeException(__TRAIT__.' can only be used by a class that extends PHPUnit_Framework_TestCase');
        } else {
            return new Builder($this, $functionName, $parameters, $returnValue, $asserts);
        }
    }
}

/*EOF*/
