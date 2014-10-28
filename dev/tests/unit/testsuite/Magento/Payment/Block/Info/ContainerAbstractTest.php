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
 * Test class for \Magento\Payment\Block\Info\AbstractContainer
 */
namespace Magento\Payment\Block\Info;

class ContainerAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testSetInfoTemplate()
    {
        $block = $this->getMock(
            'Magento\Payment\Block\Info\AbstractContainer',
            array('getChildBlock', 'getPaymentInfo'),
            array(),
            '',
            false
        );
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $paymentInfo = $objectManagerHelper->getObject('Magento\Payment\Model\Info');
        $adapterFactoryMock = $this->getMock(
            'Magento\Framework\Logger\AdapterFactory',
            array('create'),
            array(),
            '',
            false
        );
        $methodInstance = $objectManagerHelper->getObject(
            'Magento\OfflinePayments\Model\Checkmo',
            array('logAdapterFactory' => $adapterFactoryMock)
        );
        $paymentInfo->setMethodInstance($methodInstance);
        $block->expects($this->atLeastOnce())->method('getPaymentInfo')->will($this->returnValue($paymentInfo));

        $childBlock = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template');
        $block->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->with(
            'payment.info.checkmo'
        )->will(
            $this->returnValue($childBlock)
        );

        $template = 'any_template.phtml';
        $this->assertNotEquals($template, $childBlock->getTemplate());
        $block->setInfoTemplate('checkmo', $template);
        $this->assertEquals($template, $childBlock->getTemplate());
    }
}
