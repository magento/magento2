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
            array('isPageLayoutDesignAbstraction'),
            array(),
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
            array('create'),
            array(),
            '',
            false
        );
        $processorFactoryMock->expects($this->exactly(2))->method('create')->will(
            $this->returnCallback(
                function ($data) use ($processorMock, $layoutUtility) {
                    return $data === array() ? $processorMock : $layoutUtility->getLayoutUpdateFromFixture(
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
            array(
                'name' => 'design_abstractions',
                'id' => 'design_abstraction_select',
                'class' => 'design-abstraction-select',
                'title' => 'Design Abstraction Select'
            )
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
