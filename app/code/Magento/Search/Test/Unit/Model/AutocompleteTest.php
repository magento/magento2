<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\Autocomplete;
use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AutocompleteTest extends TestCase
{
    /**
     * @var Autocomplete
     */
    private $model;

    /**
     * @var DataProviderInterface|MockObject
     */
    private $firstDataProvider;

    /**
     * @var DataProviderInterface|MockObject
     */
    private $secondDataProvider;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->firstDataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMock();
        $this->secondDataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMock();
        $dataProviders = [
            '20' => $this->firstDataProvider,
            '10' => $this->secondDataProvider
        ];

        $this->model = $helper->getObject(
            Autocomplete::class,
            ['dataProviders' => $dataProviders]
        );
    }

    public function testGetItems()
    {
        $firstItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $secondItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
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
