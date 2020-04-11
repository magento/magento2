<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block\Info;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Payment\Block\Info\Substitution;
use Magento\Payment\Model\Info;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubstitutionTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $layout;

    /**
     * @var Substitution
     */
    protected $block;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->layout = $this->getMockBuilder(
            LayoutInterface::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $eventManager = $this->getMockBuilder(
            ManagerInterface::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $scopeConfig = $this->getMockBuilder(
            ScopeConfigInterface::class
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
            ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue(
                false
            )
        );

        $context = $this->getMockBuilder(
            Context::class
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
            Substitution::class,
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
            AbstractBlock::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $childAbstractBlock = clone($abstractBlock);

        $abstractBlock->expects($this->any())->method('getParentBlock')->will($this->returnValue($childAbstractBlock));

        $this->layout->expects($this->any())->method('getParentName')->will($this->returnValue('parentName'));
        $this->layout->expects($this->any())->method('getBlock')->will($this->returnValue($abstractBlock));

        $infoMock = $this->getMockBuilder(
            Info::class
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $methodMock = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $infoMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodMock));
        $this->block->setInfo($infoMock);

        $fakeBlock = new \StdClass();
        $this->layout->expects(
            $this->any()
        )->method(
            'createBlock'
        )->with(
            Template::class,
            '',
            ['data' => ['method' => $methodMock, 'template' => 'Magento_Payment::info/substitution.phtml']]
        )->will($this->returnValue($fakeBlock));

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
