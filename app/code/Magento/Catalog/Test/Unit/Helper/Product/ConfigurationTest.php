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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $object;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $contextMock = $this->createMock(Context::class);
        $optionFactoryMock = $this->createMock(OptionFactory::class);
        $filterManagerMock = $this->createMock(FilterManager::class);
        $stringUtilsMock = $this->createMock(StringUtils::class);
        $this->serializerMock = $this->createMock(Json::class);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            Configuration::class,
            [
                'context' => $contextMock,
                'productOptionFactory' => $optionFactoryMock,
                'filter' => $filterManagerMock,
                'string' => $stringUtilsMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Retrieves product additional options
     */
    public function testGetAdditionalOptionOnly()
    {
        $additionalOptionResult = ['additional_option' => 1];

        $itemMock = $this->createMock(ItemInterface::class);
        $optionMock = $this->createMock(OptionInterface::class);
        $additionalOptionMock = $this->createMock(OptionInterface::class);
        $productMock = $this->createMock(Product::class);

        $this->serializerMock->expects($this->once())->method('unserialize')->willReturn($additionalOptionResult);
        $optionMock->expects($this->once())->method('getValue')->willReturn('');
        $additionalOptionMock->expects($this->once())->method('getValue');

        $itemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $itemMock->method('getOptionByCode')->willReturnMap([
            ['option_ids', $optionMock],
            ['additional_options', $additionalOptionMock]
        ]);

        $this->assertEquals($additionalOptionResult, $this->object->getCustomOptions($itemMock));
    }
}
