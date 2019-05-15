<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 */
class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Address
     */
    private $block;

    /**
     * @var QuoteSession|MockObject
     */
    private $quoteSession;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->quoteSession = $this->getMockBuilder(QuoteSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'getStore', 'getStoreId', 'getQuote'])
            ->getMock();

        $this->block = $this->objectManager->create(
            Address::class,
            ['sessionQuote' => $this->quoteSession]
        );
    }

    /**
     * Checks address collection.
     *
     * @magentoDataFixture Magento/Customer/Fixtures/customer_2_addresses.php
     */
    public function testGetAddressCollection()
    {
        $website = $this->getWebsite('base');
        $customer = $this->getCustomer('customer@example.com', (int)$website->getId());
        $addresses = $customer->getAddresses();
        $this->quoteSession->method('getCustomerId')
            ->willReturn($customer->getId());

        $actual = $this->block->getAddressCollection();
        self::assertNotEmpty($actual);
        self::assertEquals($addresses, $actual);
    }

    /**
     * Checks address collection output encoded to json.
     *
     * @magentoDataFixture Magento/Customer/Fixtures/customer_sec_website_2_addresses.php
     * @magentoDbIsolation enabled
     */
    public function testGetAddressCollectionJson()
    {
        $website = $this->getWebsite('test');
        $customer = $this->getCustomer('customer.web@example.com', (int)$website->getId());

        $store = $this->getStore('fixture_second_store');
        $this->quoteSession->method('getStore')
            ->willReturn($store);
        $this->quoteSession->method('getCustomerId')
            ->willReturn($customer->getId());
        $addresses = $customer->getAddresses();
        $expected = [
            0 => [
                'firstname' => false,
                'lastname' => false,
                'company' => false,
                'street' => '',
                'city' => false,
                'country_id' => 'US',
                'region' => false,
                'region_id' => false,
                'postcode' => false,
                'telephone' => false,
                'vat_id' => false,
            ],
            $addresses[0]->getId() => [
                'firstname' => 'John',
                'lastname' => 'Smith',
                'company' => false,
                'street' => 'Green str, 67',
                'city' => 'Culver City',
                'country_id' => 'US',
                'region' => 'California',
                'region_id' => 12,
                'postcode' => '90230',
                'telephone' => '3468676',
                'vat_id' => false,
                'prefix' => false,
                'middlename' => false,
                'suffix' => false,
                'fax' => false
            ],
            $addresses[1]->getId() => [
                'telephone' => '845454465',
                'postcode' => '10178',
                'country_id' => 'DE',
                'city' => 'Berlin',
                'street' => 'Tunnel Alexanderpl',
                'firstname' => 'John',
                'lastname' => 'Smith',
                'company' => false,
                'region' => false,
                'region_id' => 0,
                'vat_id' => false,
                'prefix' => false,
                'middlename' => false,
                'suffix' => false,
                'fax' => false
            ]
        ];

        $actual = json_decode($this->block->getAddressCollectionJson(), true);
        self::assertEquals($expected, $actual);
    }

    /**
     * Checks one line address formatting
     */
    public function testGetAddressAsString()
    {
        $data = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'company' => 'Test Company',
            'street' => 'Green str, 67',
            'city' => 'Culver City',
            'country_id' => 'US',
            'region' => 'California',
            'region_id' => 12,
            'postcode' => '90230',
            'telephone' => '3468676',
        ];
        $address = $this->objectManager->create(AddressInterface::class, ['data' => $data]);
        $expected = 'John Smith, Green str, 67, Culver City, California 90230, United States';
        self::assertEquals($expected, $this->block->getAddressAsString($address));
    }

    public function testGetForm()
    {
        $expectedFields = [
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'company',
            'street',
            'city',
            'country_id',
            'region',
            'region_id',
            'postcode',
            'telephone',
            'fax',
            'vat_id',
        ];

        $form = $this->block->getForm();
        self::assertEquals(1, $form->getElements()->count(), 'Form has invalid number of fieldsets');

        /** @var Fieldset $fieldset */
        $fieldset = $form->getElements()[0];
        self::assertEquals(
            count($expectedFields),
            $fieldset->getElements()->count(),
            'Form has invalid number of fields'
        );

        /** @var AbstractElement $element */
        foreach ($fieldset->getElements() as $element) {
            self::assertTrue(
                in_array($element->getId(), $expectedFields),
                sprintf('Unexpected field "%s" in form.', $element->getId())
            );
        }

        /** @var \Magento\Framework\Data\Form\Element\Select $countryIdField */
        $countryIdField = $fieldset->getElements()->searchById('country_id');
        $actual = Xpath::getElementsCountForXpath('//option', $countryIdField->getElementHtml());
        self::assertEquals($this->getNumberOfCountryOptions(), $actual);
    }

    /**
     * Gets customer entity.
     *
     * @param string $email
     * @param int $websiteId
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer(string $email, int $websiteId): CustomerInterface
    {
        /** @var CustomerRepositoryInterface $repository */
        $repository = $this->objectManager->get(CustomerRepositoryInterface::class);
        return $repository->get($email, $websiteId);
    }

    /**
     * Gets website by code.
     *
     * @param string $code
     * @return WebsiteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getWebsite(string $code): WebsiteInterface
    {
        /** @var WebsiteRepositoryInterface $repository */
        $repository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        return $repository->get($code);
    }

    /**
     * Gets store by code.
     *
     * @param string $code
     * @return StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStore(string $code): StoreInterface
    {
        /** @var StoreRepositoryInterface $repository */
        $repository = $this->objectManager->get(StoreRepositoryInterface::class);
        return $repository->get($code);
    }

    /**
     * @return int
     */
    private function getNumberOfCountryOptions()
    {
        /** @var Collection $countryCollection */
        $countryCollection = $this->objectManager->create(Collection::class);
        return count($countryCollection->toOptionArray());
    }
}
