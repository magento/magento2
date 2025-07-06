<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule;

use Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider;
use Magento\Store\Model\System\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsitesOptionsProviderTest extends TestCase
{
    /**
     * @var WebsitesOptionsProvider
     */
    private $model;

    /**
     * @var MockObject
     */
    private $storeMock;

    protected function setup(): void
    {
        $this->storeMock = $this->createMock(Store::class);
        $this->model = new WebsitesOptionsProvider($this->storeMock);
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
