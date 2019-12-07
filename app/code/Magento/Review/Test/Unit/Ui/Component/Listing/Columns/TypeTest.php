<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Review\Ui\Component\Listing\Columns\Type;
use Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns\AbstractColumnTest;

/**
 * Class TypeTest
 */
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
                        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
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
                        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        'type' => __('Administrator'),
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}
