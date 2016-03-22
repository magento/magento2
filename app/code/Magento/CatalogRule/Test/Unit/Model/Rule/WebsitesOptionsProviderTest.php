<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

class WebsitesOptionsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    protected function setup()
    {
        $this->storeMock = $this->getMock('\Magento\Store\Model\System\Store', [], [], '', false);
        $this->model = new \Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider($this->storeMock);
    }

    public function testToOptionArray()
    {
        $options = [
            ['label' => 'label', 'value' => 'value']
        ];
        $this->storeMock->expects($this->once())->method('getWebsiteValuesForForm')->willReturn($options);
        $this->assertEquals($options, $this->model->toOptionArray());
    }
}
