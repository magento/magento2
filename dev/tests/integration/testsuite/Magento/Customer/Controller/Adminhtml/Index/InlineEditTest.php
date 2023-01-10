<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Observer\AfterAddressSaveObserver;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test inline edit action on customers grid.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEditTest extends AbstractBackendController
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SerializerInterface */
    private $json;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var AttributeRepository */
    private $attributeRepository;

    /** @var AddressRepositoryInterface */
    private $addressRepository;

    /** @var Registry */
    private $coreRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->get(AttributeRepository::class);
        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->coreRegistry = Bootstrap::getObjectManager()->get(Registry::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     *
     * @return void
     */
    public function testInlineEditAction(): void
    {
        $firstCustomer = $this->customerRepository->get('customer@example.com');
        $secondCustomer = $this->customerRepository->get('customer_two@example.com');
        $defaultWebsiteId = $this->websiteRepository->get('base')->getId();
        $genderId = $this->attributeRepository->get(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'gender')
            ->getSource()->getOptionId('Male');
        $params = [
            'items' => [
                $firstCustomer->getId() => [
                    CustomerInterface::EMAIL => 'updated_customer@example.com',
                    CustomerInterface::GROUP_ID => 2,
                    CustomerInterface::WEBSITE_ID => $defaultWebsiteId,
                    CustomerInterface::TAXVAT => 123123,
                    CustomerInterface::GENDER => $genderId,
                ],
                $secondCustomer->getId() => [
                    CustomerInterface::EMAIL => 'updated_customer_two@example.com',
                    CustomerInterface::GROUP_ID => 3,
                    CustomerInterface::WEBSITE_ID => $defaultWebsiteId,
                    CustomerInterface::TAXVAT => 456456,
                    CustomerInterface::GENDER => $genderId,
                ],
            ],
            'isAjax' => true,
        ];
        $actual = $this->performInlineEditRequest($params);
        $this->assertEmpty($actual['messages']);
        $this->assertFalse($actual['error']);
        $this->assertCustomersData($params);
    }

    /**
     * @dataProvider inlineEditParametersDataProvider
     *
     * @param array $params
     * @return void
     */
    public function testInlineEditWithWrongParams(array $params): void
    {
        $actual = $this->performInlineEditRequest($params);
        $this->assertEquals([(string)__('Please correct the data sent.')], $actual['messages']);
        $this->assertTrue($actual['error']);
    }

    /**
     * @return array
     */
    public function inlineEditParametersDataProvider(): array
    {
        return [
            [
                'items' => [],
                'isAjax' => true,
            ],
            [
                'items' => [],
            ],
        ];
    }

    /**
     * Customer group should not change after saving customer via customer grid because of disabled address validation
     *
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/viv_invalid_group 2
     * @magentoDataFixture Magento/Customer/_files/customer_one_address.php
     *
     * @return void
     */
    public function testInlineEditActionWithAddress(): void
    {
        $customer = $this->getCustomer();
        $params = [
            'items' => [
                $customer->getId() => []
            ],
            'isAjax' => true,
        ];
        $actual = $this->performInlineEditRequest($params);
        $updatedCustomer = $this->customerRepository->get('customer_one_address@test.com');
        $this->assertEmpty($actual['messages']);
        $this->assertFalse($actual['error']);
        $this->assertEquals(
            $customer->getGroupId(),
            $updatedCustomer->getGroupId(),
            'Customer group was changed!'
        );
    }

    /**
     * Change customer address with setting country from EU and setting VAT number
     *
     * @return CustomerInterface
     */
    private function getCustomer(): CustomerInterface
    {
        $customer = $this->customerRepository->get('customer_one_address@test.com');
        $address = $this->addressRepository->getById((int)$customer->getDefaultShipping());
        $address->setVatId(12345);
        $address->setCountryId('DE');
        $address->setRegionId(0);
        $this->addressRepository->save($address);
        $this->coreRegistry->unregister(AfterAddressSaveObserver::VIV_PROCESSED_FLAG);
        //return customer after address repository save
        return $this->customerRepository->get('customer_one_address@test.com');
    }

    /**
     * Perform inline edit request.
     *
     * @param array $params
     * @return array
     */
    private function performInlineEditRequest(array $params): array
    {
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/inlineEdit');

        return $this->json->unserialize($this->getResponse()->getBody());
    }

    /**
     * Assert customers data.
     *
     * @param array $data
     * @return void
     */
    private function assertCustomersData(array $data): void
    {
        foreach ($data['items'] as $customerId => $expectedData) {
            $customerData = $this->customerRepository->getById($customerId)->__toArray();
            foreach ($expectedData as $key => $value) {
                $this->assertEquals($value, $customerData[$key]);
            }
        }
    }
}
