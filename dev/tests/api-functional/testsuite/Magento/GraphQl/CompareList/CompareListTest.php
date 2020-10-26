<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CompareList;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for Compare list feature
 */
class CompareListTest extends GraphQlAbstract
{
    /**
     * Create compare list without product
     */
    public function testCreateCompareListWithoutProducts()
    {
        $mutation =  <<<MUTATION
mutation{
  createCompareList {
	 uid
     items {
        sku
      }
  }
}
MUTATION;
        $response = $this->graphQlMutation($mutation);
        $uid = $response['createCompareList']['uid'];
        $this->uidAssertion($uid);
    }

    /**
     * Create compare list with products
     */
    public function testCreateCompareListWithProducts()
    {
        $mutation =  <<<MUTATION
mutation{
  createCompareList(input:{products: [?, ?]}){
	 uid
     items {
        sku
      }
  }
}
MUTATION;
    }


    /**
     * Assert UID
     *
     * @param string $uid
     */
    private function uidAssertion(string $uid)
    {
        $this->assertIsString($uid);
        $this->assertEquals(32, strlen($uid));
    }
}