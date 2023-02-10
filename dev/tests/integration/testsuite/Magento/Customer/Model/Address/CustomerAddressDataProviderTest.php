<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Assert that only relevant addresses for the allowed countries under a website/store fetch.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAddressDataProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerAddressDataProvider
     */
    private $addressDataProvider;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->addressDataProvider = $this->objectManager->create(CustomerAddressDataProvider::class);
    }

    /**
     * Ensure that config changes are deleted or restored.
     */
    protected function tearDown(): void
    {
        /** @var Registry $registry */
        $registry = $this->objectManager->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var ConfigInterface $config */
        $config = $this->objectManager->get(ConfigInterface::class);
        $config->deleteConfig('general/country/allow');
        $this->objectManager->get(ReinitableConfigInterface::class)->reinit();

        /** @var Writer $configWriter */
        $configWriter = $this->objectManager->get(WriterInterface::class);

        $configWriter->save('customer/account_share/scope', 1);
        $scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $scopeConfig->clean();

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
        parent::tearDown();
    }

    /**
     * Assert that only allowed country address fetched.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @dataProvider createAddressesDataProvider
     *
     * @return void
     */
    public function testGetAddressDataByCustomerForAllowedCountries($customerId, $allowedCountries) : void
    {
        /** @var ConfigInterface $config */
        $config = $this->objectManager->get(ConfigInterface::class);
        $config->saveConfig('general/country/allow', implode(',', $allowedCountries));
        $this->objectManager->get(ReinitableConfigInterface::class)->reinit();

        /** @var Writer $configWriter */
        $configWriter = $this->objectManager->get(WriterInterface::class);
        $configWriter->save('customer/account_share/scope', 0);
        $scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $scopeConfig->clean();

        $customer = $this->customerRepository->getById($customerId);
        $addresses = $this->addressDataProvider->getAddressDataByCustomer($customer);

        $this->assertIsArray($addresses);

        foreach ($addresses as $address) {
            $this->assertContains($address['country_id'], $allowedCountries);
        }
    }

    /**
     * Data provider for create allowed or not allowed countries.
     *
     * @return array
     */
    public function createAddressesDataProvider(): array
    {
        return [
            'address_in_single_allowed_country' => [1, ['US']],
            'address_not_in_single_allowed_country' => [1, ['FR']],
            'address_in_multiple_allowed_countries' => [1, ['US', 'IN']],
            'address_not_in_multiple_allowed_countries' => [1, ['FR', 'DE']],
        ];
    }
}
