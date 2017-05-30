<?php

namespace Potherca\Phpunit\MockFunction;

use InvalidArgumentException;
use PHPUnit_Framework_Exception;
use PHPUnit\Framework\TestCase;

class Builder
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @var string */
    private $functionName;
    /** @var Generator */
    private $generator;
    /** @var array */
    private $parameters;
    /** @var array */
    private $asserts;
    /** @var mixed */
    private $returnValue;
    /** @var TestCase */
    private $testCase;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return MockFunctionObject
     *
     * @throws InvalidArgumentException
     * @throws PHPUnit_Framework_Exception
     */
    final public function getMock()
    {
        $object = $this->generator->getMock(
            $this->testCase,
            $this->functionName,
            $this->parameters,
            $this->returnValue,
            $this->asserts
        );

        // @TODO: Emulate $this->testCase->registerMockObject($object);

        return $object;
    }

    /**
     * @param array $asserts
     *
     * @return $this
     */
    final public function setAsserts(array $asserts = [])
    {
        $this->asserts = $asserts;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setMockFunctionName($name)
    {
        $this->functionName = (string) $name;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    final public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param $returnValue
     *
     * @return $this
     */
    final public function setReturnValue($returnValue)
    {
        $this->returnValue = (string) $returnValue;

        return $this;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param TestCase $testCase
     * @param string $functionName
     * @param array $parameters
     * @param array $asserts
     * @param null $returnValue
     */
    final public function __construct(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        $this->asserts = $asserts;
        $this->functionName = $functionName;
        $this->generator = new Generator();
        $this->parameters = $parameters;
        $this->returnValue = $returnValue;
        $this->testCase  = $testCase;
    }

    /**
     * @param string $name
     * @param mixed $assertion
     *
     * @return $this
     */
    final public function addAssert($name, $assertion)
    {
        $this->parameters[$name] = $assertion;

        return $this;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
}

/*EOF*/
