<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogCms;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Test category cms fields are resolved correctly
 */
class CategoryBlockTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testCategoryCmsBlock()
    {
        $blockId = 'fixture_block';
        /** @var BlockRepositoryInterface $blockRepository */
        $blockRepository = Bootstrap::getObjectManager()->get(BlockRepositoryInterface::class);
        $block = $blockRepository->getById($blockId);
        $filter = Bootstrap::getObjectManager()->get(FilterEmulate::class);
        $renderedContent = $filter->filter($block->getContent());

        /** @var CategoryRepositoryInterface $categoryRepository */
        $categoryRepository = Bootstrap::getObjectManager()->get(CategoryRepositoryInterface::class);
        $category = $categoryRepository->get(401);
        $category->setLandingPage($block->getId());
        $categoryRepository->save($category);

        $query = <<<QUERY
{
    category(id: 401){
        name
        cms_block{
            identifier
            title
            content
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNotEmpty($response['category']);
        $actualBlock = $response['category']['cms_block'];

        $this->assertEquals($block->getTitle(), $actualBlock['title']);
        $this->assertEquals($block->getIdentifier(), $actualBlock['identifier']);
        $this->assertEquals($renderedContent, $actualBlock['content']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category_tree.php
     */
    public function testCategoryWithNoCmsBlock()
    {
        $query = <<<QUERY
{
    category(id: 401){
        name
        cms_block{
            identifier
            title
            content
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $response);
        $this->assertNotEmpty($response['category']);
        $this->assertArrayHasKey('cms_block', $response['category']);
        $this->assertEquals(null, $response['category']['cms_block']);
    }
}
