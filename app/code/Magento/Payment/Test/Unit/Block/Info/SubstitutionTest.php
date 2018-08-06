<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Payment\Test\Unit\Block\Info;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubstitutionTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->layout = $this->getMockBuilder(
            \Magento\Framework\View\LayoutInterface::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $eventManager = $this->getMockBuilder(
            \Magento\Framework\Event\ManagerInterface::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $scopeConfig = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
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
            \Magento\Framework\View\Element\Template\Context::class
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
            \Magento\Payment\Block\Info\Substitution::class,
            [
                'context' => $context,
                'data' => [
                    'template' => null,
                ]
            ]
        );
    }

    public function testBeforeToHtml()
    {
        $abstractBlock = $this->getMockBuilder(
            \Magento\Framework\View\Element\AbstractBlock::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $childAbstractBlock = clone($abstractBlock);

        $abstractBlock->expects($this->any())->method('getParentBlock')->will($this->returnValue($childAbstractBlock));

        $this->layout->expects($this->any())->method('getParentName')->will($this->returnValue('parentName'));
        $this->layout->expects($this->any())->method('getBlock')->will($this->returnValue($abstractBlock));

        $infoMock = $this->getMockBuilder(
            \Magento\Payment\Model\Info::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $methodMock = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $infoMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodMock));
        $this->block->setInfo($infoMock);

        $fakeBlock = new \stdClass();
        $this->layout->expects(
            $this->any()
        )->method(
            'createBlock'
        )->with(
            \Magento\Framework\View\Element\Template::class,
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
