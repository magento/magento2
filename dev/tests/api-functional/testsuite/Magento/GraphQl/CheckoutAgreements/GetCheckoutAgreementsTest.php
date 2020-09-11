<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CheckoutAgreements;

use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\CheckoutAgreements\Model\Agreement as AgreementModel;
use Magento\CheckoutAgreements\Model\AgreementFactory;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Get checkout agreements test
 */
class GetCheckoutAgreementsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     * @magentoConfigFixture default_store checkout/options/enable_agreements 1
     */
    public function testGetActiveAgreement()
    {
        $query = $this->getQuery();

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        self::assertCount(1, $agreements);
        self::assertEquals('Checkout Agreement (active)', $agreements[0]['name']);
        self::assertEquals('Checkout agreement content: <b>HTML</b>', $agreements[0]['content']);
        self::assertEquals('200px', $agreements[0]['content_height']);
        self::assertEquals('Checkout agreement checkbox text.', $agreements[0]['checkbox_text']);
        self::assertTrue($agreements[0]['is_html']);
        self::assertEquals('AUTO', $agreements[0]['mode']);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture fixture_second_store_store checkout/options/enable_agreements 1
     */
    public function testGetActiveAgreementOnSecondStore()
    {
        $secondStoreCode = 'fixture_second_store';
        $agreementsName = 'Checkout Agreement (active)';

        $query = $this->getQuery();
        $this->assignAgreementsToStore($secondStoreCode, $agreementsName);

        $headerMap['Store'] = $secondStoreCode;
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        self::assertCount(1, $agreements);
        self::assertEquals($agreementsName, $agreements[0]['name']);
        self::assertEquals('Checkout agreement content: <b>HTML</b>', $agreements[0]['content']);
        self::assertEquals('200px', $agreements[0]['content_height']);
        self::assertEquals('Checkout agreement checkbox text.', $agreements[0]['checkbox_text']);
        self::assertTrue($agreements[0]['is_html']);
        self::assertEquals('AUTO', $agreements[0]['mode']);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture fixture_second_store_store checkout/options/enable_agreements 1
     */
    public function testGetActiveAgreementFromSecondStoreOnDefaultStore()
    {
        $secondStoreCode = 'fixture_second_store';
        $agreementsName = 'Checkout Agreement (active)';

        $query = $this->getQuery();
        $this->assignAgreementsToStore($secondStoreCode, $agreementsName);

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        self::assertEmpty($agreements);
    }

    public function testGetAgreementNotSet()
    {
        $query = $this->getQuery();

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        self::assertEmpty($agreements);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     * @magentoConfigFixture default_store checkout/options/enable_agreements 0
     */
    public function testDisabledAgreements()
    {
        $query = $this->getQuery();

        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        self::assertEmpty($agreements);
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return
            <<<QUERY
{
  checkoutAgreements {
    agreement_id
    name
    content
    content_height
    checkbox_text
    is_html
    mode
  }
}
QUERY;
    }

    /**
     * @param string $storeCode
     * @param string $agreementsName
     * @return void
     */
    private function assignAgreementsToStore(string $storeCode, string $agreementsName): void
    {
        $agreementsFactory = ObjectManager::getInstance()->get(AgreementFactory::class);
        /** @var Agreement $agreementsResource */
        $agreementsResource = ObjectManager::getInstance()->get(Agreement::class);
        /** @var StoreManagerInterface $storeManager */
        $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $store = $storeManager->getStore($storeCode);
        /** @var AgreementModel $agreements */
        $agreements = $agreementsFactory->create();
        $agreementsResource->load($agreements, $agreementsName, AgreementInterface::NAME);
        $agreements->setData('stores', [$store->getId()]);
        $agreementsResource->save($agreements);
    }
}
