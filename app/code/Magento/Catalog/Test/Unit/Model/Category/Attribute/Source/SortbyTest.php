<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Category\Attribute\Source;

use Magento\Catalog\Model\Category\Attribute\Source\Sortby;
use Magento\Catalog\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SortbyTest extends TestCase
{
    /**
     * @var Sortby
     */
    private $model;

    public function testGetAllOptions()
    {
        $validResult = [['label' => __('Position'), 'value' => 'position'], ['label' => __('fl'), 'value' => 'fc']];
        $this->assertEquals($validResult, $this->model->getAllOptions());
    }

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Sortby::class,
            [
                'catalogConfig' => $this->getMockedConfig()
            ]
        );
    }

    /**
     * @return Config
     */
    private function getMockedConfig()
    {
        $mockBuilder = $this->getMockBuilder(Config::class);
        $mockBuilder->disableOriginalConstructor();
        $mock = $mockBuilder->getMock();

        $mock->expects($this->any())
            ->method('getAttributesUsedForSortBy')
            ->willReturn([['frontend_label' => 'fl', 'attribute_code' => 'fc']]);

        return $mock;
    }
}
