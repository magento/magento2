<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $this->secondDataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
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
            ->setMockClassName('FirstItem')
            ->getMock();
        $secondItemMock = $this->getMockBuilder(Item::class)
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
