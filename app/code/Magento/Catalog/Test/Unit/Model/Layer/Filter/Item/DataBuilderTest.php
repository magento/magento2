<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\Item;

use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class DataBuilderTest extends TestCase
{
    /**
     * @var DataBuilder
     */
    protected $dataBuilder;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataBuilder = $objectManagerHelper->getObject(
            DataBuilder::class,
            []
        );
    }

    public function testBuild()
    {
        $data = [
            [
                'label' => 'Test label',
                'value' => 34,
                'count' => 21235,
            ],
            [
                'label' => 'New label for test',
                'value' => 2344,
                'count' => 122,
            ],
        ];

        foreach ($data as $item) {
            $this->dataBuilder->addItemData($item['label'], $item['value'], $item['count']);
        }

        $actualData = $this->dataBuilder->build();
        $this->assertEquals($data, $actualData);
    }
}
