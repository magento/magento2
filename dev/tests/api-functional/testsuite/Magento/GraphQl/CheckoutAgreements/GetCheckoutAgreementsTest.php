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
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Get checkout agreements test
 */
class GetCheckoutAgreementsTest extends GraphQlAbstract
{
    /**
     * @var string
     */
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

        // TODO: remove usage of the Config, use ConfigFixture instead https://github.com/magento/graphql-ce/issues/167
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
     */
    public function testDisabledAgreements()
    {
        $query = $this->getQuery();
        $this->saveAgreementConfig(0);

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
    private function saveAgreementConfig(int $value): void
    {
        $this->config->saveConfig(
            $this->agreementsXmlConfigPath,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->reinitConfig();
    }

    /**
     * Delete config
     *
     * @return void
     */
    private function deleteAgreementConfig(): void
    {
        $this->config->deleteConfig(
            $this->agreementsXmlConfigPath,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
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
