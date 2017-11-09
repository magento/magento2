<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Plugin\Eav\AttributeSetRepository;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Plugin\Eav\AttributeSetRepository\RemoveProductUrlRewrite;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for RemoveProductUrlRewrite plugin.
 */
class RemoveProductUrlRewriteTest extends TestCase
{
    /**
     * @var RemoveProductUrlRewrite
     */
    private $testSubject;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlPersist;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->testSubject = $objectManager->getObject(
            RemoveProductUrlRewrite::class,
            [
                'collectionFactory' => $this->collectionFactory,
                'urlPersist' => $this->urlPersist,
            ]
        );
    }

    /**
     * Test plugin will delete all url rewrites for products with given attribute set.
     */
    public function testAroundDelete()
    {
        $attributeSetId = '1';
        $productId = '1';

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects(self::once())
            ->method('addFieldToFilter')
            ->with(self::identicalTo('attribute_set_id'), self::identicalTo(['eq' => $attributeSetId]));
        $collection->expects(self::once())
            ->method('getAllIds')
            ->willReturn([$productId]);

        $this->collectionFactory->expects(self::once())
            ->method('create')
            ->willReturn($collection);

        $this->urlPersist->expects(self::once())
            ->method('deleteByData')
            ->with(self::identicalTo(
                [
                    UrlRewrite::ENTITY_ID => [$productId],
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]
            ));
        /** @var AttributeSetRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $attributeSetRepository */
        $attributeSetRepository = $this->getMockBuilder(AttributeSetRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $proceed = function () {
            return true;
        };

        /** @var AttributeSetInterface|\PHPUnit_Framework_MockObject_MockObject $attributeSet */
        $attributeSet = $this->getMockBuilder(AttributeSetInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeSet->expects(self::once())
            ->method('getId')
            ->willReturn($attributeSetId);

        $this->testSubject->aroundDelete($attributeSetRepository, $proceed, $attributeSet);
    }
}
