<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Model\GetPageByIdentifier;
use Magento\Framework\App\Request\Http;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\GraphQl\Controller\GraphQl;

/**
 * Test caching works for CMS page
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 */
class CmsPageCacheTest extends \Magento\TestFramework\Indexer\TestCase
{
    const CONTENT_TYPE = 'application/json';

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var GraphQl */
    private $graphql;

    /** @var SerializerInterface */
    private $jsonSerializer;

    /** @var MetadataPool */
    private $metadataPool;

    /** @var Http */
    private $request;

    /** @var \Magento\Framework\App\Response\Http */
    private $response;

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
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->graphql = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
        $this->metadataPool = $this->objectManager->get(MetadataPool::class);
        $this->request = $this->objectManager->get(Http::class);
    }
    /**
     * Test cache tags and debug header for category and querying only cms page
     *
     * @magentoCache all enabled
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testDispatchWithGetForCacheDebugHeadersAndCacheTagsForCmsPage(): void
    {
        $cmsPage = $this->objectManager->get(GetPageByIdentifier::class)->execute('page100', 0);
        $pageId = $cmsPage->getId();

        $query =
            <<<QUERY
        {
         cmsPage(id: $pageId) {
                   url_key
                   title
                   content
                   content_heading
                   page_layout
                   meta_title
                   meta_description
                   meta_keywords
                   }
         }
QUERY;
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('GET');
        $this->request->setQueryValue('query', $query);
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->graphql->dispatch($this->request);
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->objectManager->get(\Magento\Framework\App\Response\Http::class);
        /** @var  $registry \Magento\Framework\Registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->register('use_page_cache_plugin', true, true);
        $result->renderResult($response);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $actualCacheTags = explode(',', $response->getHeader('X-Magento-Tags')->getFieldValue());
        $expectedCacheTags = ['cms_p', 'cms_p_' .$pageId , 'FPC'];
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
