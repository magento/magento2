<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\Request\Http;
use Magento\GraphQl\Controller\GraphQl;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test caching works for CMS blocks
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 */
class BlockCacheTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @var Http
     */
    private $request;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new \LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->request = $this->objectManager->create(Http::class);
        $this->enableFullPageCache();
    }

    /**
     * Test that the correct cache tags get added to request for cmsBlocks
     *
     * @magentoDataFixture Magento/Cms/_files/block.php
     */
    public function testCmsBlocksRequestHasCorrectTags(): void
    {
        $blockIdentifier = 'fixture_block';
        $blockRepository = $this->objectManager->get(BlockRepository::class);
        $block = $blockRepository->getById($blockIdentifier);

        $query
            = <<<QUERY
 {
    cmsBlocks(identifiers: ["$blockIdentifier"]) {
        items {
            title
    	    identifier
            content
        }
    }
}
QUERY;

        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setQueryValue('query', $query);
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->graphqlController->dispatch($this->request);
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->objectManager->get(\Magento\Framework\App\Response\Http::class);
        $result->renderResult($response);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block->getId(), 'cms_b_' . $block->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        foreach ($expectedCacheTags as $expectedCacheTag) {
            $this->assertContains($expectedCacheTag, $actualCacheTags);
        }
    }

    /**
     * Enable full page cache so plugins are called
     */
    private function enableFullPageCache()
    {
        /** @var  $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->register('use_page_cache_plugin', true, true);

        /** @var \Magento\Framework\App\Cache\StateInterface $cacheState */
        $cacheState = $this->objectManager->get(\Magento\Framework\App\Cache\StateInterface::class);
        $cacheState->setEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER, true);
    }
}
