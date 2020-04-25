<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

use Magento\Framework\Config\Data;
use Magento\Sales\Model\Order\Pdf\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var Data|MockObject
     */
    protected $_dataStorage;

    protected function setUp(): void
    {
        $this->_dataStorage = $this->createMock(Data::class);
        $this->_model = new Config($this->_dataStorage);
    }

    public function testGetRenderersPerProduct()
    {
        $configuration = ['product_type_one' => 'Renderer_One', 'product_type_two' => 'Renderer_Two'];
        $this->_dataStorage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            "renderers/page_type",
            []
        )->willReturn(
            $configuration
        );

        $this->assertSame($configuration, $this->_model->getRenderersPerProduct('page_type'));
    }

    public function testGetTotals()
    {
        $configuration = ['total1' => ['title' => 'Title1'], 'total2' => ['title' => 'Title2']];

        $this->_dataStorage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'totals',
            []
        )->willReturn(
            $configuration
        );

        $this->assertSame($configuration, $this->_model->getTotals());
    }
}
