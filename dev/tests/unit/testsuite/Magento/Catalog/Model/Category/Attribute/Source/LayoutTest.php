<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute\Source;

use Magento\TestFramework\Helper\ObjectManager;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    private $testArray = ['test1', ['test1']];
    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Source\Layout
     */
    private $model;

    public function testGetAllOptions()
    {
        $assertArray = $this->testArray;
        array_unshift($assertArray, ['value' => '', 'label' => __('No layout updates')]);
        $this->assertEquals($assertArray, $this->model->getAllOptions());
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Category\Attribute\Source\Layout',
            [
                'pageLayoutBuilder' => $this->getMockedPageLayoutBuilder()
            ]
        );
    }

    /**
     * @return \Magento\Core\Model\PageLayout\Config\Builder
     */
    private function getMockedPageLayoutBuilder()
    {
        $mockPageLayoutConfig = $this->getMockBuilder('Magento\Framework\View\PageLayout\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPageLayoutConfig->expects($this->any())
            ->method('toOptionArray')
            ->will($this->returnValue($this->testArray));

        $mockPageLayoutBuilder = $this->getMockBuilder('Magento\Core\Model\PageLayout\Config\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPageLayoutBuilder->expects($this->once())
            ->method('getPageLayoutsConfig')
            ->will($this->returnValue($mockPageLayoutConfig));

        return $mockPageLayoutBuilder;
    }
}
