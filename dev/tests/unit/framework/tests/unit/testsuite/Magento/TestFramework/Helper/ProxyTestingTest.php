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
namespace Magento\TestFramework\Helper;

class ProxyTestingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param mixed $proxiedResult
     * @param string|null $proxiedMethod
     * @param string|null $proxiedParams
     * @param string $callProxiedMethod
     * @param array $passProxiedParams
     * @param mixed $expectedResult
     *
     * @dataProvider invokeWithExpectationsDataProvider
     */
    public function testInvokeWithExpectations(
        $method,
        $params,
        $proxiedResult,
        $proxiedMethod,
        $proxiedParams,
        $callProxiedMethod,
        $passProxiedParams,
        $expectedResult
    ) {
        // Create proxied object with $callProxiedMethod
        $proxiedObject = $this->getMock('stdClass', array($callProxiedMethod));

        // Create object, which reacts on called $method by calling $callProxiedMethod from proxied object
        $callProxy = function () use ($proxiedObject, $callProxiedMethod, $passProxiedParams) {
            return call_user_func_array(array($proxiedObject, $callProxiedMethod), $passProxiedParams);
        };

        $object = $this->getMock('stdClass', array($method));
        $builder = $object->expects($this->once())->method($method);
        call_user_func_array(array($builder, 'with'), $params);
        $builder->will($this->returnCallback($callProxy));

        // Test it
        $helper = new \Magento\TestFramework\Helper\ProxyTesting();
        $result = $helper->invokeWithExpectations(
            $object,
            $proxiedObject,
            $method,
            $params,
            $proxiedResult,
            $proxiedMethod,
            $proxiedParams
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function invokeWithExpectationsDataProvider()
    {
        return array(
            'all parameters passed' => array(
                'method' => 'returnAplusB',
                'params' => array(3, 4),
                'proxiedResult' => 7,
                'proxiedMethod' => 'returnAplusB',
                'proxiedParams' => array(3, 4),
                'callProxiedMethod' => 'returnAplusB',
                'passProxiedParams' => array(3, 4),
                'expectedResult' => 7
            ),
            'proxied method and params are to be set from proxy method and params' => array(
                'method' => 'returnAplusB',
                'params' => array(3, 4),
                'proxiedResult' => 7,
                'proxiedMethod' => null,
                'proxiedParams' => null,
                'callProxiedMethod' => 'returnAplusB',
                'passProxiedParams' => array(3, 4),
                'expectedResult' => 7
            ),
            'proxy and proxied method and params differ' => array(
                'method' => 'returnAminusBminusC',
                'params' => array(10, 1, 2),
                'proxiedResult' => 7,
                'proxiedMethod' => 'returnAminusB',
                'proxiedParams' => array(10, 3),
                'callProxiedMethod' => 'returnAminusB',
                'passProxiedParams' => array(10, 3),
                'expectedResult' => 7
            )
        );
    }
}
