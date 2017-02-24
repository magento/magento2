<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ListSortTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\ListSort
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogConfig;

    protected function setUp()
    {
        $this->catalogConfig = $this->getMockBuilder(\Magento\Catalog\Model\Config::class)
            ->disableOriginalConstructor()->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Config\Source\ListSort::class,
            ['catalogConfig' => $this->catalogConfig]
        );
    }

    public function testToOptionalArray()
    {
        $except = [
            ['label' => __('Position'), 'value' => 'position'],
            ['label' => 'testLabel', 'value' => 'testAttributeCode'],
        ];
        $this->catalogConfig->expects($this->any())->method('getAttributesUsedForSortBy')
            ->will($this->returnValue([['frontend_label' => 'testLabel', 'attribute_code' => 'testAttributeCode']]));

        $this->assertEquals($except, $this->model->toOptionArray());
    }
}
