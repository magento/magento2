<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SortbyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Source\Sortby
     */
    private $model;

    public function testGetAllOptions()
    {
        $validResult = [['label' => __('Position'), 'value' => 'position'], ['label' => __('fl'), 'value' => 'fc']];
        $this->assertEquals($validResult, $this->model->getAllOptions());
    }

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Category\Attribute\Source\Sortby::class,
            [
                'catalogConfig' => $this->getMockedConfig()
            ]
        );
    }

    /**
     * @return \Magento\Catalog\Model\Config
     */
    private function getMockedConfig()
    {
        $mockBuilder = $this->getMockBuilder(\Magento\Catalog\Model\Config::class);
        $mockBuilder->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getAttributesUsedForSortBy')
            ->will($this->returnValue([['frontend_label' => 'fl', 'attribute_code' => 'fc']]));

        return $mock;
    }
}
