<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Escaper;

class ConfigurationTest extends TestCase
{
    /**
     * @var Json|MockObject
     */
    protected $serializer;

    /**
     * @var Configuration
     */
    protected $helper;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $optionFactoryMock = $this->createMock(OptionFactory::class);
        $filterManagerMock = $this->createMock(FilterManager::class);
        $stringUtilsMock = $this->createMock(StringUtils::class);
        $this->serializer = $this->createMock(Json::class);
        $this->escaper = $this->createMock(Escaper::class);

        $this->helper = new Configuration(
            $contextMock,
            $optionFactoryMock,
            $filterManagerMock,
            $stringUtilsMock,
            $this->serializer,
            $this->escaper
        );
    }

    /**
     * Retrieves product additional options
     */
    public function testGetAdditionalOptionOnly()
    {
        $additionalOptionResult = ['additional_option' => 1];

        $itemMock = $this->getMockForAbstractClass(ItemInterface::class);
        $optionMock = $this->createMock(
            OptionInterface::class
        );
        $additionalOptionMock = $this->createMock(
            OptionInterface::class
        );
        $productMock = $this->createMock(Product::class);

        $this->serializer->expects($this->once())->method('unserialize')->willReturn($additionalOptionResult);
        $optionMock->expects($this->once())->method('getValue')->willReturn(null);
        $additionalOptionMock->expects($this->once())->method('getValue');

        $itemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $itemMock->expects($this->any())->method('getOptionByCode')->willReturnMap([
            ['option_ids', $optionMock],
            ['additional_options', $additionalOptionMock]
        ]);

        $this->assertEquals($additionalOptionResult, $this->helper->getCustomOptions($itemMock));
    }
}
