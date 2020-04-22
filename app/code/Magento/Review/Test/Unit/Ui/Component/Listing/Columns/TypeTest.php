<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns\AbstractColumnTest;
use Magento\Review\Ui\Component\Listing\Columns\Type;
use Magento\Store\Model\Store;

class TypeTest extends AbstractColumnTest
{
    /**
     * @return Type
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(Type::class, [
            'context' => $this->contextMock,
            'uiComponentFactory' => $this->uiComponentFactoryMock,
            'components' => [],
            'data' => [],
        ]);
    }

    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'customer_id' => 1
                    ],
                    [
                        'store_id' => 1,
                    ],
                    [
                        'store_id' => Store::DEFAULT_STORE_ID,
                    ],
                ],
            ],
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'customer_id' => 1,
                        'type' => __('Customer'),
                    ],
                    [
                        'store_id' => 1,
                        'type' => __('Guest'),
                    ],
                    [
                        'store_id' => Store::DEFAULT_STORE_ID,
                        'type' => __('Administrator'),
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}
