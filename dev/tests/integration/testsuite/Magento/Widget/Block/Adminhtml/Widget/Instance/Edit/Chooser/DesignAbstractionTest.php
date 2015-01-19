<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * @magentoAppArea adminhtml
 */
class DesignAbstractionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\DesignAbstraction|
     *      \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $layoutUtility = new \Magento\Framework\View\Utility\Layout($this);
        $appState = $objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $processorMock = $this->getMock(
            'Magento\Framework\View\Layout\Processor',
            ['isPageLayoutDesignAbstraction'],
            [],
            '',
            false
        );
        $processorMock->expects($this->exactly(2))->method('isPageLayoutDesignAbstraction')->will(
            $this->returnCallback(
                function ($abstraction) {
                    return $abstraction['design_abstraction'] === 'page_layout';
                }
            )
        );
        $processorFactoryMock = $this->getMock(
            'Magento\Framework\View\Layout\ProcessorFactory',
            ['create'],
            [],
            '',
            false
        );
        $processorFactoryMock->expects($this->exactly(2))->method('create')->will(
            $this->returnCallback(
                function ($data) use ($processorMock, $layoutUtility) {
                    return $data === [] ? $processorMock : $layoutUtility->getLayoutUpdateFromFixture(
                        glob(__DIR__ . '/_files/layout/*.xml')
                    );
                }
            )
        );

        $this->_block = new DesignAbstraction(
            $objectManager->get('Magento\Framework\View\Element\Template\Context'),
            $processorFactoryMock,
            $objectManager->get('Magento\Core\Model\Resource\Theme\CollectionFactory'),
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
