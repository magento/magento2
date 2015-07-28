<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\DataProvider\Customer;

use Magento\Customer\Ui\DataProvider\Customer\CustomerDataProvider;

class CustomerDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Resource\Customer\Grid\ServiceCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Resource\Customer\Grid\ServiceCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /** @var \Magento\Customer\Ui\DataProvider\Customer\CustomerDataProvider */
    protected $dataProvider;

    public function setUp()
    {
        $this->serviceCollectionFactory = $this->getMock(
            'Magento\Customer\Model\Resource\Customer\Grid\ServiceCollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->collection = $this->getMock(
            'Magento\Customer\Model\Resource\Customer\Grid\ServiceCollection',
            [],
            [],
            '',
            false
        );
        $this->serviceCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);

        $this->dataProvider = new CustomerDataProvider('', '', '', $this->serviceCollectionFactory);
    }

    public function testGetData()
    {
        $this->collection->expects($this->once())
            ->method('toArray');

        $this->dataProvider->getData();
    }

    public function testAddOrder()
    {
        $field = 'field';
        $direction = 'direction';
        $this->collection->expects($this->once())
            ->method('setOrder')
            ->with($field, $direction);

        $this->dataProvider->addOrder($field, $direction);
    }
}
