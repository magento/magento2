<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\DownloadableProduct;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for downloadable product.
 */
class DownloadableProductViewTest extends GraphQlAbstract
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @magentoApiDataFixture Magento/Downloadable/_files/downloadable_product_with_files_and_sample_url.php
     */
    public function testQueryAllFieldsDownloadableProductsWithDownloadableFileAndSample()
    {
        $productSku = 'downloadable-product';
        $query = <<<QUERY
{
  products(filter:{sku: {eq:"{$productSku}"}})
  {
       items{
           id
           attribute_set_id
           created_at
           name
           sku
           type_id
           updated_at
        price{
        regularPrice{
          amount{
            value
            currency
          }
          adjustments{
            code
            description
          }
        }
      }
           ... on DownloadableProduct {
            links_title
            links_purchased_separately

            downloadable_product_links{
              sample_url
              sort_order
              title
              price
            }
            downloadable_product_samples{
              title
              sort_order
              sort_order
              sample_url
            }
           }
       }
   }
}
QUERY;

        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            \Magento\Downloadable\Model\Link::XML_PATH_CONFIG_IS_SHAREABLE,
            0
        );
        $response = $this->graphQlQuery($query);
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $downloadableProduct = $productRepository->get($productSku, false, null, true);
        $this->assertNull($downloadableProduct->getWeight());
        $IsLinksPurchasedSeparately = $downloadableProduct->getLinksPurchasedSeparately();
        $linksTitle = $downloadableProduct->getLinksTitle();
        $this->assertEquals(
            $IsLinksPurchasedSeparately,
            $response['products']['items'][0]['links_purchased_separately']
        );
        $this->assertEquals($linksTitle, $response['products']['items'][0]['links_title']);
        $this->assertDownloadableProductLinks($downloadableProduct, $response['products']['items'][0]);
        $this->assertDownloadableProductSamples($downloadableProduct, $response['products']['items'][0]);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDownloadableProductQueryWithNoSample()
    {
        $productSku = 'downloadable-product';
        $query = <<<QUERY
{
  products(filter:{sku: {eq:"{$productSku}"}})
  {
       items{
           id
           attribute_set_id
           created_at
           name
           sku
           type_id
           updated_at
           ...on PhysicalProductInterface{
          weight
          }
        price{
        regularPrice{
          amount{
            value
            currency
          }
          adjustments{
            code
            description
          }
        }
      }
           ... on DownloadableProduct {
            links_title
            links_purchased_separately

            downloadable_product_links{
              sample_url
              sort_order
              title
              price
            }
            downloadable_product_samples{
              title
              sort_order
              sample_url
            }
           }
       }
   }
}
QUERY;
        $response = $this->graphQlQuery($query);
        /**
         * @var ProductRepositoryInterface $productRepository
         */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $downloadableProduct = $productRepository->get($productSku, false, null, true);
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            \Magento\Downloadable\Model\Link::XML_PATH_CONFIG_IS_SHAREABLE,
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            1
        );
        $IsLinksPurchasedSeparately = $downloadableProduct->getLinksPurchasedSeparately();
        $linksTitle = $downloadableProduct->getLinksTitle();
        $this->assertEquals(
            $IsLinksPurchasedSeparately,
            $response['products']['items'][0]['links_purchased_separately']
        );
        $this->assertEquals($linksTitle, $response['products']['items'][0]['links_title']);
        $this->assertEmpty($response['products']['items'][0]['downloadable_product_samples']);
        $this->assertNotEmpty(
            $response['products']['items'][0]['downloadable_product_links'],
            "Precondition failed: 'downloadable_product_links' must not be empty"
        );
        /** @var LinkInterface $downloadableProductLinks */
        $downloadableProductLinks = $downloadableProduct->getExtensionAttributes()->getDownloadableProductLinks();
        $downloadableProductLink = $downloadableProductLinks[0];
        $this->assertResponseFields(
            $response['products']['items'][0]['downloadable_product_links'][0],
            [
                'sort_order' => $downloadableProductLink->getSortOrder(),
                'title' => $downloadableProductLink->getTitle(),
                'price' => $downloadableProductLink->getPrice()
            ]
        );
    }

    /**
     * @param ProductInterface $product
     * @param  array $actualResponse
     */
    private function assertDownloadableProductLinks($product, $actualResponse)
    {
        $this->assertNotEmpty(
            $actualResponse['downloadable_product_links'],
            "Precondition failed: 'downloadable_product_links' must not be empty"
        );
        /** @var LinkInterface $downloadableProductLinks */
        $downloadableProductLinks = $product->getExtensionAttributes()->getDownloadableProductLinks();
        $downloadableProductLink = $downloadableProductLinks[1];
        $this->assertNotEmpty($actualResponse['downloadable_product_links'][1]['sample_url']);
        $this->assertResponseFields(
            $actualResponse['downloadable_product_links'][1],
            [
                'sort_order' => $downloadableProductLink->getSortOrder(),
                'title' => $downloadableProductLink->getTitle(),
                'price' => $downloadableProductLink->getPrice()
            ]
        );
    }

    /**
     * @param ProductInterface $product
     * @param $actualResponse
     */
    private function assertDownloadableProductSamples($product, $actualResponse)
    {
        $this->assertNotEmpty(
            $actualResponse['downloadable_product_samples'],
            "Precondition failed: 'downloadable_product_samples' must not be empty"
        );
        /** @var SampleInterface $downloadableProductSamples */
        $downloadableProductSamples = $product->getExtensionAttributes()->getDownloadableProductSamples();
        $downloadableProductSample = $downloadableProductSamples[0];
        $this->assertNotEmpty($actualResponse['downloadable_product_samples'][0]['sample_url']);
        $this->assertResponseFields(
            $actualResponse['downloadable_product_samples'][0],
            [
                'title' => $downloadableProductSample->getTitle(),
                'sort_order' => $downloadableProductSample->getSortOrder()
            ]
        );
    }
}
