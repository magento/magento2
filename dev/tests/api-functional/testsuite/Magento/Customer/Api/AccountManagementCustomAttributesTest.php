<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

use Magento\Customer\Model\AccountManagement;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test class for Customer's custom attributes
 */
class AccountManagementCustomAttributesTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'customerAccountManagementV1';
    const RESOURCE_PATH = '/V1/customers';

    /**
     * Sample values for testing
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const ATTRIBUTE_VALUE = 'attribute_value';

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var array
     */
    private $currentCustomerId;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var \Magento\Framework\Api\Data\ImageFactory
     */
    private $imageFactory;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\AccountManagementInterface'
        );

        $this->customerHelper = new CustomerHelper();

        $this->dataObjectProcessor = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Reflection\DataObjectProcessor'
        );

        $this->imageFactory = Bootstrap::getObjectManager()->get('Magento\Framework\Api\ImageContentFactory');
    }

    public function tearDown()
    {
        if (!empty($this->currentCustomerId)) {
            foreach ($this->currentCustomerId as $customerId) {
                $serviceInfo = [
                    'rest' => [
                        'resourcePath' => self::RESOURCE_PATH . '/' . $customerId,
                        'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
                    ],
                    'soap' => [
                        'service' => CustomerRepositoryTest::SERVICE_NAME,
                        'serviceVersion' => self::SERVICE_VERSION,
                        'operation' => CustomerRepositoryTest::SERVICE_NAME . 'DeleteById',
                    ],
                ];

                $response = $this->_webApiCall($serviceInfo, ['customerId' => $customerId]);

                $this->assertTrue($response);
            }
        }
        unset($this->accountManagement);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/attribute_user_defined_custom_attribute.php
     */
    public function testCreateCustomer()
    {
        $testImagePath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test_image.jpg';
        $imageData = base64_encode(file_get_contents($testImagePath));
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'CreateAccount',
            ],
        ];

        $image = $this->imageFactory->create()
            ->setMimeType('png')
            ->setName('sample.png')
            ->setBase64EncodedData($imageData);

        $imageData = $this->dataObjectProcessor->buildOutputDataArray(
            $image,
            '\Magento\Framework\Api\Data\ImageContentInterface'
        );

        $customerData = $this->customerHelper->createSampleCustomerDataObject();

        $customerDataArray = $this->dataObjectProcessor->buildOutputDataArray(
            $customerData,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $customerDataArray['custom_attributes'][] = [
            'attribute_code' => 'customer_image',
            'value' => $imageData,
        ];
        $requestData = [
            'customer' => $customerDataArray,
            'password' => \Magento\TestFramework\Helper\Customer::PASSWORD
        ];
        $customerData = $this->_webApiCall($serviceInfo, $requestData);
        $this->currentCustomerId[] = $customerData['id'];
    }
}
