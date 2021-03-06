<?php

namespace Potherca\Phpunit\MockFunction;

use InvalidArgumentException;
use PHPUnit_Framework_Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit_Util_InvalidArgumentHelper as InvalidArgumentHelper;

class Generator
{
    private static $mockFunctions = [];

    /**
     * @param string $functionName
     *
     * @return MockFunctionObject
     */
    public static function getMockFunction($functionName)
    {
        $key = self::createKey($functionName);
        // @FIXME: Throw exception / fail if MockObject does not exist
        return self::$mockFunctions[$key];
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

        $key = self::createKey($functionName);

        $globalVariableName = '_'. $key;

        if (array_key_exists($key, self::$mockFunctions) === false) {
            self::$mockFunctions[$key] = $this->createMockFunction($testCase, $functionName, $parameters);
        }

        $mockFunction = self::$mockFunctions[$key];
        $mockFunction->setAsserts($asserts);
        $mockFunction->setReturnValue($returnValue);

        return $mockFunction;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @param TestCase $testCase
     * @param string $functionName
     * @param array $parameters
     *
     * @return MockFunctionObject
     *
     * @throws InvalidArgumentException
     */
    private function createMockFunction(TestCase $testCase, $functionName, array $parameters = [])
    {
        /* At this point the function is guarantied to have a namespace */
        $position = strrpos($functionName,'\\');

        $function = substr($functionName, $position + 1);
        $namespace = substr($functionName, 0, $position);

        $globalVariableName = '_'.md5($functionName);

        $mockFunction = new MockFunctionObject($testCase, $function, $parameters);

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

    /**
     * @param $functionName
     *
     * @return string
     */
    private static function createKey($functionName)
    {
        return md5($functionName);
    }
}

/*EOF*/
