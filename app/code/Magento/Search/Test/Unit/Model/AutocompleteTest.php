<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Search\Model\Autocomplete;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\Autocomplete\DataProviderInterface;

class AutocompleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Autocomplete
     */
    private $model;

    /**
     * @var DataProviderInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $firstDataProvider;

    /**
     * @var DataProviderInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $secondDataProvider;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->firstDataProvider = $this->getMockBuilder(\Magento\Search\Model\DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $this->secondDataProvider = $this->getMockBuilder(\Magento\Search\Model\DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $dataProviders = [
            '20' => $this->firstDataProvider,
            '10' => $this->secondDataProvider
        ];

        $this->model = $helper->getObject(
            \Magento\Search\Model\Autocomplete::class,
            ['dataProviders' => $dataProviders]
        );
    }

    public function testGetItems()
    {
        $firstItemMock = $this->getMockBuilder(\Magento\Search\Model\Autocomplete\Item::class)
            ->disableOriginalConstructor()
            ->setMockClassName('FirstItem')
            ->getMock();
        $secondItemMock = $this->getMockBuilder(\Magento\Search\Model\Autocomplete\Item::class)
            ->disableOriginalConstructor()
            ->setMockClassName('SecondItem')
            ->getMock();

        $this->firstDataProvider->expects($this->once())
            ->method('getItems')
            ->willReturn([$firstItemMock]);
        $this->secondDataProvider->expects($this->once())
            ->method('getItems')
            ->willReturn([$secondItemMock]);

        $this->assertEquals([$secondItemMock, $firstItemMock], $this->model->getItems());
    }
}
