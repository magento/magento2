<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Helper class for testing the proxy objects
 */
namespace Magento\Framework\TestFramework\Unit\Helper;

class ProxyTesting
{
    /**
     * Invoke the proxy's method, imposing expectations on proxied object, that it must be invoked as well with
     * appropriate parameters.
     *
     * @param mixed $object Proxy
     * @param \PHPUnit_Framework_MockObject_MockObject $proxiedObject
     * @param string $method Proxy's method to invoke
     * @param array $params Parameters to be passed to proxy
     * @param null $proxiedResult Result, that must be returned by the proxied object
     * @param null $expectedMethod Expected method, to be invoked in the proxied method
     * @param null $expectedParams Expected parameters, to be passed to the proxied method
     * @return mixed
     */
    public function invokeWithExpectations(
        $object,
        \PHPUnit_Framework_MockObject_MockObject $proxiedObject,
        $method,
        $params = [],
        $proxiedResult = null,
        $expectedMethod = null,
        $expectedParams = null
    ) {
        if ($expectedMethod === null) {
            $expectedMethod = $method;
        }
        if ($expectedParams === null) {
            $expectedParams = $params;
        }
        $builder = $proxiedObject->expects(
            new \PHPUnit\Framework\MockObject\Matcher\InvokedCount(1)
        )->method(
            $expectedMethod
        );
        $builder = call_user_func_array([$builder, 'with'], $expectedParams);
        $builder->will(new \PHPUnit\Framework\MockObject\Stub\ReturnStub($proxiedResult));

        return call_user_func_array([$object, $method], $params);
    }
}
