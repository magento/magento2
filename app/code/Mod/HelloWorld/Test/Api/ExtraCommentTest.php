<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorld\Test\Api;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Add Extra Comment Test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExtraCommentTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/extra/comments';
    const RESOURCE_PATH_CUSTOMER_TOKEN = "/V1/integration/customer/token";
    const REPO_SERVICE = 'extraCommentsRepositoryV1';
    const COMMENT_SERVICE = 'extraCommentsV1';
    const SERVICE_VERSION = 'V1';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var TokenModel
     */
    private $token;

    /**
     * @var CustomerInterface
     */
    private $customerData;

    /**
     * @var CustomerHelper
     */
    private $customerHelper;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $this->customerRegistry = Bootstrap::getObjectManager()->get(
            CustomerRegistry::class
        );

        $this->customerRepository = Bootstrap::getObjectManager()->get(
            CustomerRepositoryInterface::class,
            ['customerRegistry' => $this->customerRegistry]
        );

        $this->tokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);

        $this->customerHelper = new CustomerHelper();
        $this->customerData = $this->customerHelper->createSampleCustomer();

        // get token
        $this->resetTokenForCustomerSampleData();

        $this->product = Bootstrap::getObjectManager()->get(ProductInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * Ensure that fixture customer and his addresses are deleted.
     */
    public function tearDown()
    {
        $this->customerRepository = null;

        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $this->productRepository->deleteById($this->product->getSku());
        $this->customerRepository->deleteById($this->customerData['id']);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
        parent::tearDown();
    }

    /**
     * Add extra comment to the product and check it.
     */
    public function testSetExtraComment()
    {
        $productSku = 'extraproduct';
        $extraComment = 'Test comment';
        
        $this->product
            ->setName($productSku)
            ->setSku($productSku)->setPrice(1)
            ->setVisibility(4)
            ->setStatus(1)
            ->setAttributeSetId(4)
            ->setTypeId('simple');
        $this->productRepository->save($this->product);
        
        $currentCustomer = $this->customerRepository->getById($this->customerData['id']);
        $currentCustomer->getExtensionAttributes()->getExtraAbilities()[0]->setIsAllowedAddDescription(1);
        $this->customerRepository->save($currentCustomer);
        
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $this->token,
            ],
            'soap' => [
                'service' => self::COMMENT_SERVICE,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::COMMENT_SERVICE . 'SetExtraComment',
                'token' => $this->token,
            ],
        ];
        $requestData = ['product_sku' => $productSku, 'extra_comment' => $extraComment];
        
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        
        $productExtraComments = $this->productRepository->get($productSku)
            ->getExtensionAttributes()->getExtraComments()[0];
        $this->assertEquals($productSku, $productExtraComments->getProductSku());
        $this->assertEquals($this->customerData['id'], $productExtraComments->getCustomerId());
        $this->assertEquals($extraComment, $productExtraComments->getExtraComment());
    }


    /**
     * Sets the test's access token for the created customer sample data
     */
    protected function resetTokenForCustomerSampleData()
    {
        $this->resetTokenForCustomer($this->customerData[CustomerInterface::EMAIL], 'test@123');
    }

    /**
     * Sets the test's access token for a particular username and password.
     *
     * @param string $username
     * @param string $password
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function resetTokenForCustomer($username, $password)
    {
        $this->token = $this->tokenService->createCustomerAccessToken($username, $password);
        $this->customerRegistry->remove($this->customerRepository->get($username)->getId());
    }
}
