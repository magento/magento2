<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CanonicalUrlRewriteGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit\Framework\MockObject\MockObject */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit\Framework\MockObject\MockObject */
    protected $category;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlRewriteFactory;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlRewrite;

    protected function setUp(): void
    {
        $this->urlRewriteFactory = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->disableOriginalConstructor()->getMock();
        $this->category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryUrlPathGenerator = $this->getMockBuilder(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::class
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator::class,
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
        $this->urlRewrite->expects($this->any())->method('setStoreId')->with($storeId)
            ->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityId')->with($categoryId)
            ->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setEntityType')
            ->with(CategoryUrlRewriteGenerator::ENTITY_TYPE)->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setRequestPath')->with($requestPath)
            ->willReturnSelf();
        $this->urlRewrite->expects($this->any())->method('setTargetPath')->with($targetPath)
            ->willReturnSelf();
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($this->urlRewrite);
        $this->assertEquals(
            ['category.html_store_id' => $this->urlRewrite],
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->category)
        );
    }
}
