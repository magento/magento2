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
     * @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeResourceModel;

    /**
     * @var \Magento\CatalogSearch\Model\Attribute\SearchWeight
     */
    private $searchWeightPlugin;

    /**
     * @inheritDoc
     */
    protected function setUp()
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
        $this->attribute->expects($this->once())->method('isObjectNew')->willReturn(true);
        $this->attribute->expects($this->once())->method('dataHasChangedFor')->with('search_weight')->willReturn(false);
        $this->config->expects($this->once())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }

    public function testSaveNewAttributeWithChangedProperty()
    {
        $this->attribute->expects($this->once())->method('isObjectNew')->willReturn(true);
        $this->attribute->expects($this->once())->method('dataHasChangedFor')->with('search_weight')->willReturn(true);
        $this->config->expects($this->once())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }

    public function testSaveNotNewAttributeWithChangedProperty()
    {
        $this->attribute->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->attribute->expects($this->once())->method('dataHasChangedFor')->with('search_weight')->willReturn(true);
        $this->config->expects($this->once())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }

    public function testSaveNotNewAttributeWithNotChangedProperty()
    {
        $this->attribute->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->attribute->expects($this->once())->method('dataHasChangedFor')->with('search_weight')->willReturn(false);
        $this->config->expects($this->never())->method('reset');
        $this->searchWeightPlugin->aroundSave($this->attributeResourceModel, $this->closure, $this->attribute);
    }
}
