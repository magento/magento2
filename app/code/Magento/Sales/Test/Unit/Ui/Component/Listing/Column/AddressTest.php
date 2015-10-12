<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Sales\Ui\Component\Listing\Column\Address;

/**
 * Class AddressTest
 */
class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Address
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Escaper
     */
    protected $escaper;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->escaper = $this->getMock('Magento\Framework\Escaper', ['escapeHtml'], [], '', false);
        $this->model = $objectManager->getObject(
            'Magento\Sales\Ui\Component\Listing\Column\Address',
            ['escaper' => $this->escaper]
        );
    }

    public function testPrepareDataSource()
    {
        $itemName = 'itemName';
        $oldItemValue = "itemValue\n";
        $newItemValue = 'itemValue<br/>';
        $dataSource = [
            'data' => [
                'items' => [
                    [$itemName => $oldItemValue]
                ]
            ]
        ];

        $this->model->setData('name', $itemName);
        $this->escaper->expects($this->once())->method('escapeHtml')->with($newItemValue)->willReturnArgument(0);
        $dataSource = $this->model->prepareDataSource($dataSource);
        $this->assertEquals($newItemValue, $dataSource['data']['items'][0][$itemName]);
    }
}
