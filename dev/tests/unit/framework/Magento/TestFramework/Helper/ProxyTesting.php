<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class for testing the proxy objects
 */
namespace Magento\TestFramework\Helper;

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
        $params = array(),
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
            new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1)
        )->method(
            $expectedMethod
        );
        $builder = call_user_func_array(array($builder, 'with'), $expectedParams);
        $builder->will(new \PHPUnit_Framework_MockObject_Stub_Return($proxiedResult));

        return call_user_func_array(array($object, $method), $params);
    }
}
