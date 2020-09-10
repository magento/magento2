<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Helper;

use Magento\Bundle\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    protected $config;

    /**
     * @var Data
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->config = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->helper = (new ObjectManager($this))->getObject(
            Data::class,
            ['config' => $this->config]
        );
    }

    public function testGetAllowedSelectionTypes()
    {
        $configData = ['allowed_selection_types' => ['foo', 'bar', 'baz']];
        $this->config->expects($this->once())->method('getType')->with('bundle')->willReturn($configData);

        $this->assertEquals($configData['allowed_selection_types'], $this->helper->getAllowedSelectionTypes());
    }

    public function testGetAllowedSelectionTypesIfTypesIsNotSet()
    {
        $configData = [];
        $this->config->expects($this->once())->method('getType')
            ->with(Type::TYPE_BUNDLE)
            ->willReturn($configData);

        $this->assertEquals([], $this->helper->getAllowedSelectionTypes());
    }
}
