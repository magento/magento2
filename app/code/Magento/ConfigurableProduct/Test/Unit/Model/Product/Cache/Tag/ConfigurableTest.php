<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Cache\Tag;

use \Magento\ConfigurableProduct\Model\Product\Cache\Tag\Configurable;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
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
        $this->typeResource = $this->getMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class,
            [],
            [],
            '',
            false
        );

        $this->model = new Configurable($this->typeResource);
    }

    public function testGetWithScalar()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Provided argument is not an object');
        $this->model->getTags('scalar');
    }

    public function testGetTagsWithObject()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Provided argument must be a product');
        $this->model->getTags(new \StdClass);
    }

    public function testGetTagsWithVariation()
    {
        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        $identities = ['id1', 'id2'];

        $product->expects($this->once())
            ->method('getIdentities')
            ->willReturn($identities);

        $this->assertEquals($identities, $this->model->getTags($product));
    }
}
