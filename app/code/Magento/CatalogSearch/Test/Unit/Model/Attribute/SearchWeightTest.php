<?php
/**
 * *
 *  * Copyright © Magento, Inc. All rights reserved.
 *  * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Attribute;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\CatalogSearch\Model\Attribute\SearchWeight;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchWeightTest extends TestCase
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var AbstractModel|MockObject
     */
    private $attribute;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Attribute|MockObject
     */
    private $attributeResourceModel;

    /**
     * @var SearchWeight
     */
    private $searchWeightPlugin;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->setMethods(['reset'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $this->getMockBuilder(AbstractModel::class)
            ->setMethods(['isObjectNew', 'dataHasChangedFor'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributeResourceModel = $this->getMockBuilder(Attribute::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->closure = function (AbstractModel $model) {
            return $model;
        };

        $objectManager = new ObjectManager($this);
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
