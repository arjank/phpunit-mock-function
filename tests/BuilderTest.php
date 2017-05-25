<?php

namespace Foo;

use PHPUnit\Framework\TestCase;
use Potherca\Phpunit\MockFunction\Builder;

class BuilderTest extends TestCase
{
    const MOCK_RETURN = 'MockReturnValue';

    final public function testMockingUserlandFunction()
    {
        $functionName = 'mockFunction';
        $parameters = [];
        $returnValue = null;
        $asserts = [];

        $builder = new Builder($this, $functionName, $parameters, $returnValue, $asserts);

        $builder->getMock();

        $actual = get_defined_functions()['user'];

        $expected = strtolower(__NAMESPACE__.'\\'.$functionName);

        self::assertContains($expected, $actual);
    }

    final public function testMockingNativeFunction()
    {
        $expected = self::MOCK_RETURN;

        $functionName = 'get_defined_functions';
        $parameters = [];
        $returnValue = $expected;
        $asserts = [];

        $builder = new Builder($this, $functionName, $parameters, $returnValue, $asserts);

        $definedFunctions = get_defined_functions();

        $builder->getMock();

        $actual = get_defined_functions();

        self::assertEquals($expected, $actual);
    }
}

/*EOF*/
