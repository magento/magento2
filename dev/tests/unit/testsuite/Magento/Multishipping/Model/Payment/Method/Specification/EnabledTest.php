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
namespace Magento\Multishipping\Model\Payment\Method\Specification;

/**
 * Enabled method Test
 */
class EnabledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager helper
     *
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Payment config mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Payment\Model\Config
     */
    protected $paymentConfigMock;

    public function setUp()
    {
        $this->paymentConfigMock = $this->getMock('\Magento\Payment\Model\Config', array(), array(), '', false);
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * Test isSatisfiedBy method
     *
     * @param array $methodsInfo
     * @param bool $result
     * @dataProvider methodsDataProvider
     */
    public function testIsSatisfiedBy($methodsInfo, $result)
    {
        $method = 'method-name';
        $methodsInfo = array($method => $methodsInfo);

        $this->paymentConfigMock->expects(
            $this->once()
        )->method(
            'getMethodsInfo'
        )->will(
            $this->returnValue($methodsInfo)
        );

        $configSpecification = $this->objectManager->getObject(
            'Magento\Multishipping\Model\Payment\Method\Specification\Enabled',
            array('paymentConfig' => $this->paymentConfigMock)
        );

        $this->assertEquals(
            $result,
            $configSpecification->isSatisfiedBy($method),
            sprintf('Failed payment method test: "%s"', $method)
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function methodsDataProvider()
    {
        return array(
            array(array('allow_multiple_address' => 1), true),
            array(array('allow_multiple_address' => 0), false),
            array(array('no_flag' => 0), false)
        );
    }
}
