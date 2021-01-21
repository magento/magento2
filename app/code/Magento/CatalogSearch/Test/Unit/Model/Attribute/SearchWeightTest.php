<?php
/**
 * *
 *  * Copyright © Magento, Inc. All rights reserved.
 *  * See COPYING.txt for license details.
 *
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Attribute;

use Magento\CatalogSearch\Model\Attribute\SearchWeight;

class SearchWeightTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var \Magento\Framework\Model\AbstractModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Framework\Search\Request\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeResourceModel;

    /**
     * @var \Magento\CatalogSearch\Model\Attribute\SearchWeight
     */
    private $searchWeightPlugin;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(\Magento\Framework\Search\Request\Config::class)
            ->setMethods(['reset'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->setMethods(['isObjectNew', 'dataHasChangedFor'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributeResourceModel = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Attribute::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->closure = function (\Magento\Framework\Model\AbstractModel $model) {
            return $model;
        };

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchWeightPlugin = $objectManager->getObject(
            SearchWeight::class,
            [
                'config' => $this->config,
            ]
        );
    }

    public function testSaveNewAttribute()
    {
        $this->attribute->expects(self::once())->method('isObjectNew')->willReturn(true);
        $this->attribute->expects(self::once())->method('dataHasChangedFor')->with('search_weight')->willReturn(false);
        $this->config->expects(self::once())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }

    public function testSaveNewAttributeWithChangedProperty()
    {
        $this->attribute->expects(self::once())->method('isObjectNew')->willReturn(true);
        $this->attribute->expects(self::once())->method('dataHasChangedFor')->with('search_weight')->willReturn(true);
        $this->config->expects(self::once())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }

    public function testSaveNotNewAttributeWithChangedProperty()
    {
        $this->attribute->expects(self::once())->method('isObjectNew')->willReturn(false);
        $this->attribute->expects(self::once())->method('dataHasChangedFor')->with('search_weight')->willReturn(true);
        $this->config->expects(self::once())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }

    public function testSaveNotNewAttributeWithNotChangedProperty()
    {
        $this->attribute->expects(self::once())->method('isObjectNew')->willReturn(false);
        $this->attribute->expects(self::once())->method('dataHasChangedFor')->with('search_weight')->willReturn(false);
        $this->config->expects(self::never())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }
}
