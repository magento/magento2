<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model;

use Magento\ConfigurableProduct\Plugin\Model\UpdateConfigurableProductAttributeCollection;
use Magento\ConfigurableProduct\Ui\DataProvider\Attributes;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;

class UpdateConfigurableProductAttributeCollectionTest extends TestCase
{
    /**
     * @var Attributes
     */
    private Attributes $attributesMock;

    /**
     * @var Collection
     */
    private Collection $collectionMock;

    /**
     * @var Select
     */
    private Select $selectMock;

    /**
     * @var UpdateConfigurableProductAttributeCollection
     */
    private UpdateConfigurableProductAttributeCollection $plugin;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->attributesMock = $this->getMockBuilder(Attributes::class)
            ->setMethods(['getCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSelect'])
            ->getMock();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['where'])
            ->getMock();
        $this->plugin = new UpdateConfigurableProductAttributeCollection();
    }

    /**
     * @return void
     */
    public function testBeforeGetData()
    {
        $this->collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $this->attributesMock->expects($this->any())
            ->method('getCollection')
            ->willReturn($this->collectionMock);
        $this->plugin->beforeGetData($this->attributesMock);
    }
}
