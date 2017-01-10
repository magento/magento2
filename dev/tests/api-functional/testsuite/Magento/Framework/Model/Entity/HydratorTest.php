<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Entity;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\NoSuchEntityException;

class HydratorTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customerData;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    const PASSWORD = 'test@123';

    protected function setUp()
    {
        $this->_markTestAsRestOnly('Hydrator can be tested using REST adapter only');
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customerHelper = $this->objectManager->get(CustomerHelper::class);
        $this->dataObjectProcessor = $this->objectManager->create(DataObjectProcessor::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/attribute_user_defined_custom_attribute.php
     */
    public function testCreate()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/TestModuleDefaultHydrator',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ]
        ];
        $requestData = ['customer' => $this->generateCustomerData(), 'password' => self::PASSWORD];
        $expectedData = $this->_webApiCall($serviceInfo, $requestData);

        $customerId = $expectedData['id'];
        $actualData = $this->loadCustomerViaWebApi($customerId);
        $this->validateCustomerData($expectedData, $actualData);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/attribute_user_defined_custom_attribute.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdate()
    {
        $fixtureCustomerId = 1;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/TestModuleDefaultHydrator/{$fixtureCustomerId}",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ]
        ];

        $expectedData = $this->_webApiCall($serviceInfo, ['customer' => $this->generateCustomerData()]);
        $actualData = $this->loadCustomerViaWebApi($fixtureCustomerId);
        $this->validateCustomerData($expectedData, $actualData);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testDelete()
    {
        $fixtureCustomerId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/TestModuleDefaultHydrator/' . $fixtureCustomerId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ]
        ];

        $isDeleted = $this->_webApiCall($serviceInfo);
        $this->assertTrue($isDeleted);

        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->setExpectedException(
            NoSuchEntityException::class,
            "No such entity with customerId = {$fixtureCustomerId}"
        );
        $customerRepository->getById($fixtureCustomerId);
    }

    /**
     * Validate customer data.
     *
     * @param array $expectedData
     * @param array $actualData
     */
    private function validateCustomerData($expectedData, $actualData)
    {
        unset(
            $actualData['addresses'][0]['region_id'],
            $actualData['addresses'][0]['id'],
            $actualData['addresses'][1]['region_id'],
            $actualData['addresses'][1]['id'],

            $expectedData['addresses'][0]['default_shipping'],
            $expectedData['addresses'][0]['default_billing'],
            $expectedData['addresses'][1]['default_shipping'],
            $expectedData['addresses'][1]['default_billing'],

            $expectedData['created_at'],
            $expectedData['updated_at'],
            $actualData['created_at'],
            $actualData['updated_at'],
            $actualData['disable_auto_group_change']
        );

        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @return array
     */
    private function generateCustomerData()
    {
        $customer = $this->customerHelper->createSampleCustomerDataObject();

        /** @var \Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface $extensionAttribute */
        $extensionAttribute = $this->objectManager->create(
            \Magento\TestModuleDefaultHydrator\Api\Data\ExtensionAttributeInterface::class
        );
        $extensionAttribute->setValue('extension attribute value');

        /** @var \Magento\Customer\Api\Data\CustomerExtensionInterface $customerExtension */
        $customerExtension = $this->objectManager->create(
            \Magento\Customer\Api\Data\CustomerExtension::class
        );
        $customerExtension->setExtensionAttribute($extensionAttribute);
        $customer->setExtensionAttributes($customerExtension);
        $customer->setCustomAttribute('custom_attribute1', 'custom attribute value');

        $customerData = $this->dataObjectProcessor->buildOutputDataArray(
            $customer,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        return $customerData;
    }

    /**
     * @param int $customerId
     * @return array
     */
    private function loadCustomerViaWebApi($customerId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/TestModuleDefaultHydrator/' . $customerId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ]
        ];
        $customerData = $this->_webApiCall($serviceInfo);
        return $customerData;
    }
}
