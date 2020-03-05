<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Category;

use Magento\CatalogGraphQl\Model\Resolver\Category\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    private $image;

    protected function setUp()
    {
        $directoryList = $this->getMockBuilder(\Magento\Framework\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrlPath'])
            ->getMock();
        $directoryList->expects($this->once())->method('getUrlPath')
            ->will($this->returnValue('http://example.com/pub/media/'));
        $this->image = new Image($directoryList);
    }

    public function testResolve()
    {
        $field = $this->createMock(\Magento\Framework\GraphQl\Config\Element\Field::class);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->once())->method('getBaseUrl')->will($this->returnValue('http://example.com'));
        $extensionAttribute = $this
            ->createPartialMock(\Magento\GraphQl\Model\Query\ContextExtension::class, ['getStore']);
        $extensionAttribute->expects($this->once())->method('getStore')->will($this->returnValue($store));
        $context = $this->createPartialMock(\Magento\GraphQl\Model\Query\Context::class, ['getExtensionAttributes']);
        $context
            ->expects($this->once())->method('getExtensionAttributes')->will($this->returnValue($extensionAttribute));
        $info = $this->createMock(\Magento\Framework\GraphQl\Schema\Type\ResolveInfo::class);
        $categoryMock  = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getImage']
        );
        $categoryMock->expects($this->once())->method('getImage')
            ->will($this->returnValue('media/catalog/tmp/category/image.jpeg'));
        $value = ['model' =>  $categoryMock];
        $imageUrl = $this->image->resolve($field, $context, $info, $value);
        if ($imageUrl) {
            $this->assertEquals($imageUrl, 'http://example.com/media/catalog/tmp/category/image.jpeg');
        }
    }
}
