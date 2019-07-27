<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CustomerDownloadableProduct;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class CustomerDownloadableProductTest extends GraphQlAbstract
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testGetCustomerDownloadableProducts()
    {
        $query = <<<MUTATION
mutation {
  generateCustomerToken(
    email: "customer@example.com"
    password: "password"
  ) {
    token
  }
}
MUTATION;
        $response = $this->graphQlMutation($query);
        $token = $response['generateCustomerToken']['token'];
        $this->headers = ['Authorization' => 'Bearer ' . $token];

        $query = <<<QUERY
        {
    customerDownloadableProducts{
        items{
            order_increment_id
            date
            status
            download_url
            remaining_downloads
        }
    }

}
QUERY;

        $response = $this->graphQlQuery($query, [], '', $this->headers);
    }
}
