<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CheckoutAgreements\Api;

use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\CheckoutAgreements\Model\Agreement as AgreementModel;
use Magento\CheckoutAgreements\Model\AgreementFactory;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CheckoutAgreementsListTest extends GraphQlAbstract
{
    private $agreementsXmlConfigPath = 'checkout/options/enable_agreements';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->config = $this->objectManager->get(Config::class);
        $this->saveAgreementConfig(1);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     */
    public function testGetActiveAgreement()
    {
        $query = $this->getQuery();

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        $this->assertCount(1, $agreements);
        $this->assertEquals('Checkout Agreement (active)', $agreements[0]['name']);
        $this->assertEquals('Checkout agreement content: <b>HTML</b>', $agreements[0]['content']);
        $this->assertEquals('200px', $agreements[0]['content_height']);
        $this->assertEquals('Checkout agreement checkbox text.', $agreements[0]['checkbox_text']);
        $this->assertEquals(true, $agreements[0]['is_html']);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testGetActiveAgreementOnSecondStore()
    {
        $secondStoreCode = 'fixture_second_store';
        $agreementsName = 'Checkout Agreement (active)';

        $query = $this->getQuery();
        $this->assignAgreementsToStore($secondStoreCode, $agreementsName);

        $headerMap['Store'] = $secondStoreCode;
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        $this->assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        $this->assertCount(1, $agreements);
        $this->assertEquals($agreementsName, $agreements[0]['name']);
        $this->assertEquals('Checkout agreement content: <b>HTML</b>', $agreements[0]['content']);
        $this->assertEquals('200px', $agreements[0]['content_height']);
        $this->assertEquals('Checkout agreement checkbox text.', $agreements[0]['checkbox_text']);
        $this->assertEquals(true, $agreements[0]['is_html']);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testGetActiveAgreementFromSecondStoreOnDefaultStore()
    {
        $secondStoreCode = 'fixture_second_store';
        $agreementsName = 'Checkout Agreement (active)';

        $query = $this->getQuery();
        $this->assignAgreementsToStore($secondStoreCode, $agreementsName);

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        $this->assertCount(0, $agreements);
    }

    public function testGetAgreementNotSet()
    {
        $query = $this->getQuery();

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        $this->assertCount(0, $agreements);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testDisabledAgreements()
    {
        $secondStoreCode = 'fixture_second_store';
        $agreementsName = 'Checkout Agreement (active)';

        $query = $this->getQuery();
        $this->assignAgreementsToStore($secondStoreCode, $agreementsName);

        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore($secondStoreCode);
        $this->saveAgreementConfig(0, $store);

        $headerMap['Store'] = $secondStoreCode;
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        $this->assertArrayHasKey('checkoutAgreements', $response);
        $agreements = $response['checkoutAgreements'];
        $this->assertCount(0, $agreements);

        $this->deleteAgreementConfig($store);
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
        $agreementsFactory = $this->objectManager->get(AgreementFactory::class);
        /** @var Agreement $agreementsResource */
        $agreementsResource = $this->objectManager->get(Agreement::class);
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore($storeCode);
        /** @var AgreementModel $agreements */
        $agreements = $agreementsFactory->create();
        $agreementsResource->load($agreements, $agreementsName, AgreementInterface::NAME);
        $agreements->setData('stores', [$store->getId()]);
        $agreementsResource->save($agreements);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->deleteAgreementConfig();
    }

    /**
     * @param int $value
     * @param StoreInterface $store
     */
    private function saveAgreementConfig(int $value, ?StoreInterface $store = null): void
    {
        $scopeId = $store ? $store->getId() : 0;
        $scope = $store ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $this->config->saveConfig(
            $this->agreementsXmlConfigPath,
            $value,
            $scope,
            $scopeId
        );

        $this->reinitConfig();
    }

    /**
     * @param StoreInterface $store
     */
    private function deleteAgreementConfig(?StoreInterface $store = null): void
    {
        $scopeId = $store ? $store->getId() : 0;
        $scope = $store ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $this->config->deleteConfig(
            $this->agreementsXmlConfigPath,
            $scope,
            $scopeId
        );

        $this->reinitConfig();
    }

    private function reinitConfig(): void
    {
        /** @var ReinitableConfigInterface $config */
        $config = $this->objectManager->get(ReinitableConfigInterface::class);
        $config->reinit();
    }
}
