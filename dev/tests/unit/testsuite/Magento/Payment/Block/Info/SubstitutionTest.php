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

namespace Magento\Payment\Block\Info;

class SubstitutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Payment\Block\Info\Substitution
     */
    protected $block;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->layout = $this->getMockBuilder(
            'Magento\Framework\View\LayoutInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $eventManager = $this->getMockBuilder(
            'Magento\Framework\Event\ManagerInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $scopeConfig = $this->getMockBuilder(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $scopeConfig->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            $this->stringContains(
                'advanced/modules_disable_output/'
            ),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue(
                false
            )
        );

        $context = $this->getMockBuilder(
            'Magento\Framework\View\Element\Template\Context'
        )->disableOriginalConstructor()->setMethods(
            ['getLayout', 'getEventManager', 'getScopeConfig']
        )->getMock();
        $context->expects(
            $this->any()
        )->method(
            'getLayout'
        )->will(
            $this->returnValue(
                $this->layout
            )
        );
        $context->expects(
            $this->any()
        )->method(
            'getEventManager'
        )->will(
            $this->returnValue(
                $eventManager
            )
        );
        $context->expects(
            $this->any()
        )->method(
            'getScopeConfig'
        )->will(
            $this->returnValue(
                $scopeConfig
            )
        );

        $this->block = $this->objectManager->getObject(
            'Magento\Payment\Block\Info\Substitution',
            [
                'context' => $context,
                'data' => [
                    'template' => null
                ]
            ]
        );
    }

    public function testBeforeToHtml()
    {
        $abstractBlock = $this->getMockBuilder(
            'Magento\Framework\View\Element\AbstractBlock'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $childAbstractBlock = clone($abstractBlock);

        $abstractBlock->expects($this->any())->method('getParentBlock')->will($this->returnValue($childAbstractBlock));

        $this->layout->expects($this->any())->method('getParentName')->will($this->returnValue('parentName'));
        $this->layout->expects($this->any())->method('getBlock')->will($this->returnValue($abstractBlock));

        $infoMock = $this->getMockBuilder(
            'Magento\Payment\Model\Info'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $methodMock = $this->getMockBuilder(
            'Magento\Payment\Model\MethodInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $infoMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodMock));
        $this->block->setInfo($infoMock);

        $fakeBlock = new \StdClass;
        $this->layout->expects(
            $this->any()
        )->method(
            'createBlock'
        )->with(
            'Magento\Framework\View\Element\Template',
            '',
            ['data' => ['method' => $methodMock, 'template' => 'Magento_Payment::info/substitution.phtml']]
        )->will(
                $this->returnValue(
                    $fakeBlock
                )
            );

        $childAbstractBlock->expects(
            $this->any()
        )->method(
            'setChild'
        )->with(
            'order_payment_additional',
            $fakeBlock
        );

        $this->block->toHtml();
    }
}
