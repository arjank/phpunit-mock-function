<?php

namespace Potherca\Phpunit\MockFunction;

use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    const MOCK_RETURN = 'Mock return value for function "%s"';

    final public function testBuilderShouldMockUserlandFunctionWhenCalledFromTestClass()
    {
        $functionName = 'mockFunction';

        $this->createMockFunction($functionName);
    }

    final public function testBuilderShouldMockNativeFunctionWhenCalledFomTestClass()
    {
        $functionName = 'get_defined_functions';

        $this->createMockFunction($functionName);
   }

    final public function testBuilderShouldMockNativeFunctionWhenCalledForFileWithNamespace()
    {
        $file = 'native-function-in-namespace.php';
        $functionName = 'Foo\\strtolower';

        $this->createMockFunction($functionName, $file);
    }

    final public function testBuilderShouldMockNativeFunctionWhenCalledForFileWithoutNamespace()
    {
        $file = 'native-function-in-global-scope.php';
        $functionName = 'strtolower';

        $this->createMockFunction($functionName, $file);
    }

    final public function testBuilderShouldMockUserlandFunctionWhenCalledForFileWithNamespace()
    {
        $file = 'userland-function-in-namespace.php';
        $functionName = '\\Foo\\foo';

        $this->createMockFunction($functionName, $file);
    }

    final public function testBuilderShouldMockUserlandFunctionWhenCalledForFileWithFunctionFromAnotherNamespace()
    {
        $file = 'userland-function-from-another-namespace.php';
        $functionName = 'Bar\\foo';

        $this->createMockFunction($functionName, $file);
    }

    final public function testBuilderShouldMockUserlandFunctionWhenCalledForFileWithoutNamespace()
    {
        $file = 'userland-function-in-global-scope.php';
        $functionName = __NAMESPACE__.'\\foo';

        $this->createMockFunction($functionName, $file);
    }

    /**
     * @param $functionName
     * @param $file
     */
    private function createMockFunction($functionName, $file = '')
    {
        $functionName = ltrim($functionName, '\\');

        $expectedReturnValue = vsprintf(self::MOCK_RETURN, [$functionName]);

        $builder = new Builder($this, $functionName, [], $expectedReturnValue);

        $builder->getMock();

        if (strpos($functionName, '\\') === false) {
            /* Functions in global scope are mocked in the current namespace */
            $functionName = __NAMESPACE__ . '\\' . $functionName;
        }

        if ($file !== '') {
            $actualReturnValue = requireOnce(__DIR__ . '/fixtures/' . $file);
        } else {
            $actualReturnValue = $functionName();
        }

        $message = vsprintf('Return value did not match expectation for mocked function "%s" in file "%s"', [$functionName, $file]);
        self::assertEquals($expectedReturnValue, $actualReturnValue, $message);

        $actual = \get_defined_functions()['user'];

        $expected = \strtolower($functionName);

        self::assertContains($expected, $actual);
    }
}

/*EOF*/
