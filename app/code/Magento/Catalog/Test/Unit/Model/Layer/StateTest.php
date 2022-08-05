<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    private $model;

    /**
     * @var Item|MockObject
     */
    private $item;

    protected function setUp(): void
    {
        $this->item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(State::class);
    }

    public function testSetFiltersException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->model->setFilters($this->item);
    }

    public function testSetFilters()
    {
        $expect = [$this->item];

        $this->model->setFilters($expect);
        $this->assertEquals($expect, $this->model->getFilters());
    }

    public function testAddFilter()
    {
        $expect = [$this->item];

        $this->model->addFilter($this->item);

        $this->assertEquals($expect, $this->model->getFilters());
    }
}
