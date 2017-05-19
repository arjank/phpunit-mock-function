<?php

namespace Potherca\Phpunit\MockFunction;

use PHPUnit_Framework_TestCase as TestCase;
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
     * @throws \PHPUnit_Framework_Exception
     */
    private function generateMock(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        if (!is_string($functionName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
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
     */
    private function createMockFunction(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        $globalVariableName = '_'.md5($functionName);

        global $$globalVariableName;

        $mockFunction = new MockFunctionObject($testCase, $functionName, $parameters, $returnValue, $asserts);

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
            'functionName' => $functionName,
            'globalVariableName' => $globalVariableName,
            'parameterDefinition' => $parameterDefinition,
            'parameterNames' => $parameterNames,
        ]);

        $eval = $template->render();

        eval($eval);

        return $mockFunction;
    }
}

/*EOF*/
