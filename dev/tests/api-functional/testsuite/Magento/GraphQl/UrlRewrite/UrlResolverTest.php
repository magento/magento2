<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\UrlRewrite;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's URLResolver query to verify canonical URL's are correctly returned.
 */
class UrlResolverTest extends GraphQlAbstract
{
    /** @var ObjectManager */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test for custom type which point to the invalid product/category/cms page.
     *
     * @magentoApiDataFixture Magento/UrlRewrite/_files/url_rewrite_not_existing_entity.php
     */
    public function testNonExistentEntityUrlRewrite()
    {
        $urlPath = 'non-exist-entity.html';

        $query = <<<QUERY
{
  urlResolver(url:"{$urlPath}")
  {
   id
   relative_url
   type
   redirectCode
  }
}
QUERY;

        $this->expectExceptionMessage(
            "No such entity found with matching URL key: " . $urlPath
        );
        $this->graphQlQuery($query);
    }
}
