<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQL\CheckoutAgreements\Api;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class CheckoutAgreementsListTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     */
    public function testGetActiveAgreement()
    {
        $query =
            <<<QUERY
{
  checkoutAgreements {
    agreement_id
    name
    content
    content_height
    checkbox_text
    is_html
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        $this->assertEquals(1, count($agreements));
        $this->assertEquals('Checkout Agreement (active)', $agreements[0]['name']);
        $this->assertEquals('Checkout agreement content: <b>HTML</b>', $agreements[0]['content']);
        $this->assertEquals('200px', $agreements[0]['content_height']);
        $this->assertEquals('Checkout agreement checkbox text.', $agreements[0]['checkbox_text']);
        $this->assertEquals(true, $agreements[0]['is_html']);
    }
}
