<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Sales\Ui\Component\Listing\Column\OrderActions;

/**
 * Class OrderActionsTest
 */
class OrderActionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderActions
     */
    protected $model;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilder = $this->getMockForAbstractClass('Magento\Framework\UrlInterface');
        $this->model = $objectManager->getObject(
            'Magento\Sales\Ui\Component\Listing\Column\OrderActions',
            ['urlBuilder' => $this->urlBuilder]
        );
    }

    public function testPrepareDataSource()
    {
        $entityId = 1;
        $url = 'url';
        $itemName = 'itemName';
        $oldItemValue = 'itemValue';
        $newItemValue = [
            'view' => [
                'href' => $url,
                'label' => __('View')
            ]
        ];
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        $itemName => $oldItemValue,
                        'entity_id' => $entityId
                    ]
                ]
            ]
        ];

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with(OrderActions::URL_PATH_VIEW, ['order_id' => $entityId])
            ->willReturn($url);

        $this->model->setData('name', $itemName);
        $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
