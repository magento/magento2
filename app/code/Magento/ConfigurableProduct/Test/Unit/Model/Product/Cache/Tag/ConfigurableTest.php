<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Cache\Tag;

use Magento\ConfigurableProduct\Model\Product\Cache\Tag\Configurable;

class ConfigurableTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Configurable
     */
    private $typeResource;

    /**
     * @var Configurable
     */
    private $model;

    protected function setUp()
    {
        $this->typeResource = $this->createMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class
        );

        $this->model = new Configurable($this->typeResource);
    }

    public function testGetWithScalar()
    {
        $this->expectException(\InvalidArgumentException::class, 'Provided argument is not an object');
        $this->model->getTags('scalar');
    }

    public function testGetTagsWithObject()
    {
        $this->expectException(\InvalidArgumentException::class, 'Provided argument must be a product');
        $this->model->getTags(new \StdClass());
    }

    public function testGetTagsWithVariation()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

        $identities = ['id1', 'id2'];

        $product->expects($this->once())
            ->method('getIdentities')
            ->willReturn($identities);

        $parentId = 4;
        $this->typeResource->expects($this->once())
            ->method('getParentIdsByChild')
            ->willReturn([$parentId]);

        $expected = array_merge($identities, [\Magento\Catalog\Model\Product::CACHE_TAG . '_' . $parentId]);

        $this->assertEquals($expected, $this->model->getTags($product));
    }
}
