<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Helper;

use Magento\Braintree\Helper\Country;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CountryTest
 */
class CountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\Braintree\Helper\Country
     */
    private $helper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $collectionFactory = $this->getCollectionFactoryMock();

        $this->helper = $this->objectManager->getObject(Country::class, [
            'factory' => $collectionFactory
        ]);
    }

    /**
     * @covers \Magento\Braintree\Helper\Country::getCountries
     */
    public function testGetCountries()
    {
        $this->collection->expects(static::once())
            ->method('toOptionArray')
            ->willReturn([
                ['value' => 'US', 'label' => 'United States'],
                ['value' => 'UK', 'label' => 'United Kingdom'],
            ]);

        $this->helper->getCountries();

        $this->collection->expects(static::never())
            ->method('toOptionArray');

        $this->helper->getCountries();
    }

    /**
     * Create mock for country collection factory
     */
    protected function getCollectionFactoryMock()
    {
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'loadData', 'toOptionArray', '__wakeup'])
            ->getMock();

        $this->collection->expects(static::any())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $this->collection->expects(static::any())
            ->method('loadData')
            ->willReturnSelf();

        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $collectionFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->collection);

        return $collectionFactory;
    }
}
