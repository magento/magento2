<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanonicalUrlRewriteGeneratorTest extends TestCase
{
    /** @var CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var CategoryUrlPathGenerator|MockObject */
    protected $categoryUrlPathGenerator;

    /** @var Category|MockObject */
    protected $category;

    /** @var UrlRewriteFactory|MockObject */
    protected $urlRewriteFactory;

    /** @var UrlRewrite|MockObject */
    protected $urlRewrite;

    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(UrlRewriteFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryUrlPathGenerator = $this->getMockBuilder(
            CategoryUrlPathGenerator::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->canonicalUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            CanonicalUrlRewriteGenerator::class,
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'urlRewriteFactory' => $this->urlRewriteFactory
            ]
        );
    }

    public function testGenerate()
    {
        $requestPath = 'category.html';
        $targetPath = 'target-path';
        $storeId = 'store_id';
        $categoryId = 'category_id';

        $this->category->expects($this->any())->method('getId')->willReturn($categoryId);
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->willReturn($requestPath);
        $this->categoryUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->willReturn($targetPath);
        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($categoryId)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(CategoryUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($requestPath)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($targetPath)->willReturnSelf();
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($this->urlRewrite);
        $this->assertEquals(
            ['category.html_store_id' => $this->urlRewrite],
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->category)
        );
    }
}
