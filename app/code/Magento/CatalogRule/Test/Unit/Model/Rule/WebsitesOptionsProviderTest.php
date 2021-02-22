<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

class WebsitesOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeMock;

    protected function setup(): void
    {
        $this->storeMock = $this->createMock(\Magento\Store\Model\System\Store::class);
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
