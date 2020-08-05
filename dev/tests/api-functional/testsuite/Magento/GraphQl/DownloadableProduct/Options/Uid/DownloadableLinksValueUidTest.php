<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\DownloadableProduct\Options\Uid;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for downloadable product links uid
 */
class DownloadableLinksValueUidTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/downloadable_product_with_files_and_sample_url.php
     */
    public function testQueryUidForDownloadableLinks()
    {
        $productSku = 'downloadable-product';
        $query = $this->getQuery($productSku);
        $response = $this->graphQlQuery($query);
        $responseProduct = $response['products']['items'][0];

        self::assertNotEmpty($responseProduct['downloadable_product_links']);

        foreach ($responseProduct['downloadable_product_links'] as $productLink) {
            $uid = $this->getUidByLinkId((int) $productLink['id']);
            self::assertEquals($uid, $productLink['uid']);
        }
    }

    /**
     * Get uid by link id
     *
     * @param int $linkId
     *
     * @return string
     */
    private function getUidByLinkId(int $linkId): string
    {
        return base64_encode('downloadable/' . $linkId);
    }

    /**
     * Get query
     *
     * @param string $sku
     *
     * @return string
     */
    private function getQuery(string $sku): string
    {
        return <<<QUERY
query {
  products(filter: { sku: { eq: "$sku" } }) {
    items {
      sku

      ... on DownloadableProduct {
        downloadable_product_links {
          id
          uid
        }
      }
    }
  }
}
QUERY;
    }
}
