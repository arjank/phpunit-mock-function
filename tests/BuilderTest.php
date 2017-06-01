<?php

namespace Potherca\Phpunit\MockFunction;

use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    const MOCK_RETURN = 'Mock return value for function "%s", (%s pass)';

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
     * @param string $functionName
     * @param string|null $file
     *
     * @dataProvider provideFunctionsToMock
     */
    final public function testBuilderShouldMockFunctionWhenCalledAgain($functionName, $file = '')
    {
        $this->createMockFunction($functionName, $file, 'second');
    }

    /**
     * @param string $functionName
     * @param string|null $file
     *
     * @dataProvider provideFunctionsToMock
     */
    final public function testBuilderShouldUpdateMockFunctionWhenGivenDifferentReturnValue($functionName, $file = '')
    {
        $this->createMockFunction($functionName, $file, 'third');
    }

    /**
     * @param string $functionName
     * @param string $file
     * @param string $pass
     */
    private function createMockFunction($functionName, $file = '', $pass = '')
    {
        $functionName = ltrim($functionName, '\\');

        $pass = $pass ?: 'first';


        $builder = new Builder($this, $functionName, []);

        $mockFunction = $builder->getMock();

        if (strpos($functionName, '\\') === false) {
            /* Functions in global scope are mocked in the current namespace */
            $functionName = __NAMESPACE__ . '\\' . $functionName;

            if ($file !== '') {
                /* Files are only loaded once, so $pass will always be equal to first returned value */
                $pass = 'first';
            }
        }

        $expectedReturnValue = vsprintf(self::MOCK_RETURN, [$functionName, $pass]);
        $mockFunction->setReturnValue($expectedReturnValue);

        if ($file !== '') {
            $actualReturnValue = requireOnce(__DIR__ . '/fixtures/' . $file);
        } else {
            $actualReturnValue = $functionName();
        }

        $message = vsprintf('Return value did not match expectation for mocked function "%s" (for file "%s")', [$functionName, $file]);
        self::assertEquals($expectedReturnValue, $actualReturnValue, $message);

        $actual = \get_defined_functions()['user'];

        $expected = \strtolower($functionName);

        self::assertContains($expected, $actual);
    }

    final public function provideFunctionsToMock()
    {
        return [
            'Native function in global scope' => ['strtolower', 'native-function-in-global-scope.php'],
            'Native function in namespace' => ['Foo\\strtolower', 'native-function-in-namespace.php'],
            'Native function in test class' => ['get_defined_functions'],
            'Userland function from another namespace' => ['Bar\\foo', 'userland-function-from-another-namespace.php'],
            'Userland function in global scope' => ['\\foo', 'userland-function-in-global-scope.php'],
            'Userland function in namespace' => ['\\Foo\\foo', 'userland-function-in-namespace.php'],
            'Userland function in test class' => ['mockFunction'],
        ];
    }
}

/*EOF*/
