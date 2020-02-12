<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test inline edit action on customers grid.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->get(AttributeRepository::class);
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
                    'email' => 'updated_customer@example.com',
                    'group_id' => 2,
                    'website_id' => $defaultWebsiteId,
                    'taxvat' => 123123,
                    'gender' => $genderId,
                ],
                $secondCustomer->getId() => [
                    'email' => 'updated_customer_two@example.com',
                    'group_id' => 3,
                    'website_id' => $defaultWebsiteId,
                    'taxvat' => 456456,
                    'gender' => $genderId,
                ],
            ],
            'isAjax' => true,
        ];
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/inlineEdit');
        $actual = $this->json->unserialize($this->getResponse()->getBody());
        $this->assertEquals([], $actual['messages']);
        $this->assertEquals(false, $actual['error']);
        $this->assertCustomersData($params);
    }

    /**
     * @return void
     */
    public function testInlineEditActionNoSelection(): void
    {
        $params = [
            'items' => [],
            'isAjax' => true,
        ];
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/index/inlineEdit');
        $actual = $this->json->unserialize($this->getResponse()->getBody());
        $this->assertEquals(['Please correct the data sent.'], $actual['messages']);
        $this->assertEquals(true, $actual['error']);
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
            $customer = $this->customerRepository->getById($customerId);
            $this->assertEquals($expectedData['email'], $customer->getEmail());
            $this->assertEquals($expectedData['group_id'], $customer->getGroupId());
            $this->assertEquals($expectedData['website_id'], $customer->getWebsiteId());
            $this->assertEquals($expectedData['taxvat'], $customer->getTaxvat());
            $this->assertEquals($expectedData['gender'], $customer->getGender());
        }
    }
}
