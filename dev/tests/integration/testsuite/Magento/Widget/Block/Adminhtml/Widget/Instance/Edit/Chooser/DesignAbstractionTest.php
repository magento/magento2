<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * @magentoAppArea adminhtml
 */
class DesignAbstractionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\DesignAbstraction|
     *      \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_block;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $layoutUtility = new \Magento\Framework\View\Utility\Layout($this);
        $appState = $objectManager->get(\Magento\Framework\App\State::class);
        $appState->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $processorMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ProcessorInterface::class)
            ->setMethods(['isPageLayoutDesignAbstraction'])
            ->getMockForAbstractClass();
        $processorMock->expects($this->exactly(2))->method('isPageLayoutDesignAbstraction')->willReturnCallback(
            
                function ($abstraction) {
                    return $abstraction['design_abstraction'] === 'page_layout';
                }
            
        );
        $processorFactoryMock =
            $this->createPartialMock(\Magento\Framework\View\Layout\ProcessorFactory::class, ['create']);
        $processorFactoryMock->expects($this->exactly(2))->method('create')->willReturnCallback(
            
                function ($data) use ($processorMock, $layoutUtility) {
                    return $data === [] ? $processorMock : $layoutUtility->getLayoutUpdateFromFixture(
                        glob(__DIR__ . '/_files/layout/*.xml')
                    );
                }
            
        );

        $this->_block = new DesignAbstraction(
            $objectManager->get(\Magento\Framework\View\Element\Template\Context::class),
            $processorFactoryMock,
            $objectManager->get(\Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class),
            $appState,
            [
                'name' => 'design_abstractions',
                'id' => 'design_abstraction_select',
                'class' => 'design-abstraction-select',
                'title' => 'Design Abstraction Select'
            ]
        );
    }

    public function testToHtml()
    {
        $this->assertXmlStringEqualsXmlFile(
            __DIR__ . '/_files/design-abstraction_select.html',
            $this->_block->toHtml()
        );
    }
}
