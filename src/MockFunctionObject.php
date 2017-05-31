<?php

namespace Potherca\Phpunit\MockFunction;

use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_Builder_InvocationMocker;
use PHPUnit_Framework_MockObject_InvocationMocker;
use PHPUnit_Framework_MockObject_Matcher_Invocation;

/**
 * @method PHPUnit_Framework_MockObject_Builder_InvocationMocker method($constraint)
 */
class MockFunctionObject implements \PHPUnit_Framework_MockObject_MockObject
{
    /** @var array */
    private $asserts;
    /** @var string */
    private $functionName;
    /** @var array */
    private $parameters;
    /** @var mixed */
    private $returnValue;
    /** @var TestCase */
    private $testCase;

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

    /** @param mixed $returnValue */
    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;
    }

    final public function __construct(TestCase $testCase, $functionName, array $parameters = [], $returnValue = null, array  $asserts = [])
    {
        $this->asserts = $asserts;
        $this->functionName = $functionName;
        $this->parameters = $parameters;
        $this->returnValue = $returnValue;
        $this->testCase  = $testCase;
    }

    final public function __invoke(array $parameters)
    {
        foreach ($parameters as $parameterName => $parameterValue) {

            if (in_array($parameterName, $this->parameters, true) === false) {

                $message = vsprintf(
                    'Parameter "$%s" has not been mocked for function "%s(%s)")',
                    [
                        $parameterName,
                        $this->functionName,
                        implode(', ', $this->parameters),
                    ]
                );

                $this->fail($message);
            }
            elseif (array_key_exists($parameterName, $this->asserts)) {
                $actual = $parameterValue;
                $expected = $this->asserts[$parameterName];

                if (is_callable($expected)) {
                    $expected($actual);
                } else {
                    $this->testCase->assertEquals($expected, $actual);
                }
            }/*/ There is nothing else to do... /*/
        }

        return $this->returnValue;
    }

    /**
     * @param $message
     *
     * @throws PHPUnit_Framework_AssertionFailedError
     */
    public function fail($message)
    {
        $this->testCase->fail($message);
    }

    /**
     * Registers a new expectation in the mock object and returns the match
     * object which can be infused with further details.
     *
     * @param PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     *
     * @return PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    final public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        //return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    /**
     * @return PHPUnit_Framework_MockObject_InvocationMocker
     *
     * @since  Method available since Release 2.0.0
     */
    final public function __phpunit_setOriginalObject($originalObject)
    {
        //$this->__phpunit_originalObject = $originalObject;
    }

    /**
     * @return PHPUnit_Framework_MockObject_InvocationMocker
     */
    final public function __phpunit_getInvocationMocker()
    {
        // if ($this->__phpunit_invocationMocker === null) {
        //     $this->__phpunit_invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker($this->__phpunit_configurable);
        // }
        //
        // return $this->__phpunit_invocationMocker;
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @param bool $unsetInvocationMocker
     */
    final public function __phpunit_verify($unsetInvocationMocker = true)
    {
        // $this->__phpunit_getInvocationMocker()->verify();
        //
        // if ($unsetInvocationMocker) {
        //     $this->__phpunit_invocationMocker = null;
        // }
    }

    /**
     * @return bool
     */
    final public function __phpunit_hasMatchers()
    {
        //return $this->__phpunit_getInvocationMocker()->hasMatchers();
    }
}

/*EOF*/
