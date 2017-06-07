<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper\Product;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $helper;

    protected function setUp()
    {
        $contextMock = $this->getMock(\Magento\Framework\App\Helper\Context::class, [], [], '', false);
        $optionFactoryMock = $this->getMock(\Magento\Catalog\Model\Product\OptionFactory::class, [], [], '', false);
        $filterManagerMock = $this->getMock(\Magento\Framework\Filter\FilterManager::class, [], [], '', false);
        $stringUtilsMock = $this->getMock(\Magento\Framework\Stdlib\StringUtils::class, [], [], '', false);
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\Serializer\Json::class, [], [], '', false);

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

        $itemMock = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class,
            [],
            [],
            '',
            false
        );
        $optionMock = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
            [],
            [],
            '',
            false
        );
        $additionalOptionMock = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class,
            [],
            [],
            '',
            false
        );
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        $this->serializer->expects($this->once())->method('unserialize')->willReturn($additionalOptionResult);
        $optionMock->expects($this->once())->method('getValue')->willReturn(null);
        $additionalOptionMock->expects($this->once())->method('getValue');

        $itemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $itemMock->expects($this->any())->method('getOptionByCode')->will($this->returnValueMap(
            [
                ['option_ids', $optionMock],
                ['additional_options', $additionalOptionMock]
            ]
        ));

        $this->assertEquals($additionalOptionResult, $this->helper->getCustomOptions($itemMock));
    }
}
