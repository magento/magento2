<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model\StoreSwitcher;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher;
use Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test store switching
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RewriteUrlTest extends TestCase
{
    /**
     * @var StoreSwitcher
     */
    private $storeSwitcher;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Class dependencies initialization
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeSwitcher = $this->objectManager->get(StoreSwitcher::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->storeManager = $this->objectManager->create(StoreManagerInterface::class);
    }

    /**
     * Test switching stores with non-existent cms pages and then redirecting to the homepage
     *
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     * @throws NoSuchEntityException
     */
    public function testSwitchToNonExistingPage(): void
    {
        $fromStore = $this->getStoreByCode('default');
        $toStore = $this->getStoreByCode('fixture_second_store');

        $this->setBaseUrl($toStore, 'http://domain.com/');

        $product = $this->productRepository->get('simple333');

        $redirectUrl = "http://domain.com/{$product->getUrlKey()}.html";
        $expectedUrl = $toStore->getBaseUrl();

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
        $this->setBaseUrl($toStore, 'http://localhost/');
    }

    /**
     * Testing store switching with existing cms pages
     *
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @magentoDbIsolation disabled
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     * @throws NoSuchEntityException
     */
    public function testSwitchToExistingPage(): void
    {
        $fromStore = $this->getStoreByCode('default');
        $toStore = $this->getStoreByCode('fixture_second_store');

        $redirectUrl = "http://localhost/index.php/page-c/";
        $expectedUrl = "http://localhost/index.php/page-c-on-2nd-store";

        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }

    /**
     * Testing store switching with existing cms pages with non-existing url keys
     *
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @magentoDbIsolation disabled
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException|NoSuchEntityException
     */
    public function testSwitchToExistingPageToNonExistingUrlKeys(): void
    {
        $fromStore = $this->getStoreByCode('default');
        $toStore = $this->getStoreByCode('fixture_second_store');

        //test with CMS page with url rewrite for from and target store
        $redirectUrl1 = "http://localhost/index.php/page-c/";
        $expectedUrl1 = "http://localhost/index.php/page-c-on-2nd-store";

        $this->assertEquals($expectedUrl1, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl1));

        //test with CMS page without url rewrite for second/target store
        $redirectUrl2 = "http://localhost/index.php/fixture_second_store/page-e/";
        $expectedUrl2 = "http://localhost/index.php/fixture_second_store/page-e/";

        $this->assertEquals($expectedUrl2, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl2));

        //test with custom url rewrite without CMS page
        $redirectUrl3 = "http://localhost/index.php/fixture_second_store/contact/";
        $expectedUrl3 = "http://localhost/index.php/fixture_second_store/contact/";

        $this->assertEquals($expectedUrl3, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl3));
    }

    /**
     * Testing store switching using cms pages with the same url_key but with different page_id
     *
     * @magentoDataFixture Magento/Cms/_files/two_cms_page_with_same_url_for_different_stores.php
     * @magentoDbIsolation disabled
     * @return void
     * @throws CannotSwitchStoreException|NoSuchEntityException
     */
    public function testSwitchCmsPageToAnotherStore(): void
    {
        $fromStore = $this->getStoreByCode('default');
        $toStore = $this->getStoreByCode('fixture_second_store');
        $redirectUrl = "http://localhost/index.php/page100/";
        $expectedUrl = "http://localhost/index.php/page100/";
        $this->assertEquals($expectedUrl, $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl));
    }

    /**
     * Test store switching with logged in customer on cms page with different url_key
     *
     * @magentoDataFixture Magento/UrlRewrite/_files/url_rewrite.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @return void
     * @throws CannotSwitchStoreException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function testSwitchCmsPageToAnotherStoreAsCustomer(): void
    {
        /** @var CustomerRepositoryInterface $repository */
        $repository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->loginAsCustomer($repository->get('customer@example.com'));
        $fromStore = $this->getStoreByCode('default');
        $toStore = $this->getStoreByCode('fixture_second_store');

        $redirectUrl = "http://localhost/index.php/page-c/";
        $expectedUrl = "http://localhost/index.php/page-c-on-2nd-store";

        $secureRedirectUrl = $this->storeSwitcher->switch($fromStore, $toStore, $redirectUrl);
        $this->assertEquals($expectedUrl, $secureRedirectUrl);
    }

    /**
     * Login as customer
     *
     * @param CustomerInterface $customer
     */
    private function loginAsCustomer($customer)
    {
        /** @var Session $session */
        $session = $this->objectManager->get(Session::class);
        $session->setCustomerDataAsLoggedIn($customer);
    }

    /**
     * Set base url to store.
     *
     * @param StoreInterface $targetStore
     * @param string $baseUrl
     * @return void
     * @throws Exception
     */
    private function setBaseUrl(StoreInterface $targetStore, string $baseUrl): void
    {
        $configValue = $this->objectManager->create(Value::class);
        $configValue->load('web/unsecure/base_url', 'path');
        if (!$configValue->getPath()) {
            $configValue->setPath('web/unsecure/base_url');
        }
        $configValue->setValue($baseUrl);
        $configValue->setScope(ScopeInterface::SCOPE_STORES);
        $configValue->setScopeId($targetStore->getId());
        $configValue->save();

        $reinitibleConfig = $this->objectManager->create(ReinitableConfigInterface::class);
        $reinitibleConfig->reinit();
    }

    /**
     * Get store object by storeCode
     *
     * @param string $storeCode
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStoreByCode(string $storeCode): StoreInterface
    {
        /** @var StoreRepositoryInterface */
        return $this->storeManager->getStore($storeCode);
    }
}
