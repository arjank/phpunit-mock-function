<?php

namespace Potherca\Phpunit\MockFunction;

use InvalidArgumentException;
use PHPUnit_Framework_Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit_Util_InvalidArgumentHelper as InvalidArgumentHelper;

class Generator
{
    private static $mockFunctions = [];

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @param TestCase $testCase
     * @param string $functionName
     * @param array $parameters
     * @param mixed $returnValue
     * @param array $asserts
     *
     * @return MockFunctionObject
     *
     * @throws InvalidArgumentException
     * @throws PHPUnit_Framework_Exception
     */
    final public function getMock(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        return $this->generateMock($testCase, $functionName, $parameters, $returnValue, $asserts);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @param TestCase $testCase
     * @param string $functionName
     * @param array $parameters
     * @param mixed $returnValue
     * @param array $asserts
     *
     * @return MockFunctionObject
     *
     * @throws InvalidArgumentException
     * @throws PHPUnit_Framework_Exception
     */
    public function generate(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        return $this->generateMock($testCase, $functionName, $parameters, $returnValue, $asserts);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @param TestCase $testCase
     * @param string $functionName
     * @param array $parameters
     * @param mixed $returnValue
     * @param array $asserts
     *
     * @return MockFunctionObject
     *
     * @throws InvalidArgumentException
     * @throws PHPUnit_Framework_Exception
     */
    private function generateMock(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        if (!is_string($functionName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $functionName = ltrim($functionName, '\\');

        if (strrpos($functionName,'\\') === false) {
            /* Function has no namespace, using caller namespace */
            $namespace = $this->getCallerNamespace();
            $functionName = $namespace . '\\' . $functionName;
        }

        if (array_key_exists($functionName, self::$mockFunctions) === false) {
            self::$mockFunctions[$functionName] = $this->createMockFunction($testCase, $functionName, $parameters, $returnValue, $asserts);
        }

        return self::$mockFunctions[$functionName];
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @param TestCase $testCase
     * @param string $functionName
     * @param array $parameters
     * @param mixed $returnValue
     * @param array $asserts
     *
     * @return MockFunctionObject
     *
     * @throws InvalidArgumentException
     */
    private function createMockFunction(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        /* At this point the function is guarantied to have a namespace */
        $position = strrpos($functionName,'\\');

        $function = substr($functionName, $position + 1);
        $namespace = substr($functionName, 0, $position);

        $globalVariableName = '_'.md5($functionName);

        global $$globalVariableName;

        $mockFunction = new MockFunctionObject($testCase, $function, $parameters, $returnValue, $asserts);

        $$globalVariableName = $mockFunction;

        $parameterDefinition = '';
        $parameterNames = '';

        if (count($parameters) > 0) {
            $parameterDefinition .= '$';
            $parameterDefinition .= implode(', $', $parameters);

            $parameterNames = '\''.implode('\', \'', $parameters).'\'';
        }

        $template = new \Text_Template(__DIR__.'/mockFunction.tpl', '{{', '}}');

        $template->setVar([
            'functionName' => $function,
            'globalVariableName' => $globalVariableName,
            'namespace' => $namespace,
            'parameterDefinition' => $parameterDefinition,
            'parameterNames' => $parameterNames,
        ]);

        $eval = $template->render();

        eval($eval);

        return $mockFunction;
    }

    /**
     * @return bool|string
     *
     * @throws PHPUnit_Framework_Exception
     */
    private function getCallerNamespace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        $caller = array_pop($trace);

        $class = $caller['class'];

        $position = strrpos($class,'\\');

        $namespace = substr($class, 0, $position);

        if ($namespace === '') {
            throw new PHPUnit_Framework_Exception('Function to mock can not be in global/root namespace');
        } else {
            return $namespace;
        }
    }
}

/*EOF*/
