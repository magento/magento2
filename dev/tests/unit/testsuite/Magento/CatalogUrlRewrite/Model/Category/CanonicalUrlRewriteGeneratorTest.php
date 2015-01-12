<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\TestFramework\Helper\ObjectManager;

class CanonicalUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject */
    protected $category;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewriteBuilder;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewrite;

    protected function setUp()
    {
        $this->urlRewriteBuilder = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder')
            ->disableOriginalConstructor()->getMock();
        $this->urlRewrite = $this->getMockBuilder('Magento\UrlRewrite\Service\V1\Data\UrlRewrite')
            ->disableOriginalConstructor()->getMock();
        $this->category = $this->getMockBuilder('Magento\Catalog\Model\Category')
            ->disableOriginalConstructor()->getMock();
        $this->categoryUrlPathGenerator = $this->getMockBuilder(
            'Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator'
        )->disableOriginalConstructor()->getMock();
        $this->canonicalUrlRewriteGenerator = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator',
            [
                'categoryUrlPathGenerator' => $this->categoryUrlPathGenerator,
                'urlRewriteBuilder' => $this->urlRewriteBuilder
            ]
        );
    }

    public function testGenerate()
    {
        $requestPath = 'category.html';
        $targetPath = 'target-path';
        $storeId = 'store_id';
        $categoryId = 'category_id';

        $this->category->expects($this->any())->method('getId')->will($this->returnValue($categoryId));
        $this->categoryUrlPathGenerator->expects($this->any())->method('getUrlPathWithSuffix')
            ->will($this->returnValue($requestPath));
        $this->categoryUrlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')
            ->will($this->returnValue($targetPath));
        $this->urlRewriteBuilder->expects($this->any())->method('setStoreId')->with($storeId)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setEntityId')->with($categoryId)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setEntityType')
            ->with(CategoryUrlRewriteGenerator::ENTITY_TYPE)->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setRequestPath')->with($requestPath)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('setTargetPath')->with($targetPath)
            ->will($this->returnSelf());
        $this->urlRewriteBuilder->expects($this->any())->method('create')->will($this->returnValue($this->urlRewrite));
        $this->assertEquals(
            [$this->urlRewrite],
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->category)
        );
    }
}
