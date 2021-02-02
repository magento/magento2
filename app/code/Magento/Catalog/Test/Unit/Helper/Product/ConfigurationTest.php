<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper\Product;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $helper;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $optionFactoryMock = $this->createMock(\Magento\Catalog\Model\Product\OptionFactory::class);
        $filterManagerMock = $this->createMock(\Magento\Framework\Filter\FilterManager::class);
        $stringUtilsMock = $this->createMock(\Magento\Framework\Stdlib\StringUtils::class);
        $this->serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->helper = new \Magento\Catalog\Helper\Product\Configuration(
            $contextMock,
            $optionFactoryMock,
            $filterManagerMock,
            $stringUtilsMock,
            $this->serializer
        );
    }

    /**
     * Retrieves product additional options
     */
    public function testGetAdditionalOptionOnly()
    {
        $additionalOptionResult = ['additional_option' => 1];

        $itemMock = $this->createMock(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class);
        $optionMock = $this->createMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class
        );
        $additionalOptionMock = $this->createMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class
        );
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $this->serializer->expects($this->once())->method('unserialize')->willReturn($additionalOptionResult);
        $optionMock->expects($this->once())->method('getValue')->willReturn(null);
        $additionalOptionMock->expects($this->once())->method('getValue');

        $itemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $itemMock->expects($this->any())->method('getOptionByCode')->willReturnMap(
            [
                ['option_ids', $optionMock],
                ['additional_options', $additionalOptionMock]
            ]
        );

        $this->assertEquals($additionalOptionResult, $this->helper->getCustomOptions($itemMock));
    }
}
