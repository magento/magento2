<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Model\Layout;

class MergeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fixture XML instruction(s) to be used in tests
     */
    const FIXTURE_LAYOUT_XML = '<block class="Magento\Framework\View\Element\Template" template="fixture.phtml"/>';

    /**
     * @var \Magento\Framework\View\Model\Layout\Merge
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $files = [];
        foreach (glob(__DIR__ . '/_files/layout/*.xml') as $filename) {
            $files[] = new \Magento\Framework\View\File($filename, 'Magento_Widget');
        }
        $fileSource = $this->getMockForAbstractClass('Magento\Framework\View\File\CollectorInterface');
        $fileSource->expects($this->any())->method('getFiles')->will($this->returnValue($files));

        $this->_model = $objectManager->create(
            'Magento\Framework\View\Model\Layout\Merge',
            ['fileSource' => $fileSource]
        );
    }

    public function testLoadDbApp()
    {
        $this->assertEmpty($this->_model->getHandles());
        $this->assertEmpty($this->_model->asString());
        $this->_model->getFileLayoutUpdatesXml();
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_files/merged.xml', $this->_model->asString());
    }
}
