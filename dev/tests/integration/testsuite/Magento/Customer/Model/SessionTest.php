<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Framework\App\PageCache\FormKey;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoAppIsolation enabled
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var FormKey
     */
    protected $formKey;

    /** @var PublicCookieMetadata $cookieMetadata */
    protected $cookieMetadata;

    /**
     * @var HttpResponse
     */
    private $response;

    protected function setUp(): void
    {
        $this->_customerSession = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Session::class
        );
        /** @var CookieMetadataFactory $cookieMetadataFactory */
        $cookieMetadataFactory = Bootstrap::getObjectManager()->get(CookieMetadataFactory::class);

        $this->cookieMetadata = $cookieMetadataFactory
            ->createPublicCookieMetadata();
        $this->cookieMetadata->setDomain($this->_customerSession->getCookieDomain());
        $this->cookieMetadata->setPath($this->_customerSession->getCookiePath());
        $this->cookieMetadata->setDuration($this->_customerSession->getCookieLifetime());

        $this->formKey = Bootstrap::getObjectManager()->get(FormKey::class);
        $this->formKey->set(
            'form_key',
            $this->cookieMetadata
        );
        $this->response = Bootstrap::getObjectManager()->get(ResponseInterface::class);
    }

    public function testLoginById()
    {
        $this->assertTrue($this->_customerSession->loginById(1));
        // fixture
        $this->assertTrue($this->_customerSession->isLoggedIn());
    }

    public function testLoginByIdCustomerDataLoadedCorrectly()
    {
        $fixtureCustomerId = 1;

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
        $customerSession->loginById($fixtureCustomerId);

        $customerData = $customerSession->getCustomerData();

        $this->assertEquals($fixtureCustomerId, $customerData->getId(), "Customer data was loaded incorrectly");
    }

    /**
     * Verifies that logging in flushes form_key
     */
    public function testLoginActionFlushesFormKey()
    {
        $beforeKey = $this->formKey->get();
        $this->_customerSession->loginById(1);
        $afterKey = $this->formKey->get();

        $this->assertNotEquals($beforeKey, $afterKey);
    }

    /**
     * Verifies that logging out flushes form_key
     */
    public function testLogoutActionFlushesFormKey()
    {
        $this->_customerSession->loginById(1);

        $this->formKey->set(
            'form_key',
            $this->cookieMetadata
        );

        $beforeKey = $this->formKey->get();
        $this->_customerSession->logout();
        $afterKey = $this->formKey->get();

        $this->assertNotEquals($beforeKey, $afterKey);
    }

    /**
     * Check that SID is not used in redirects.
     *
     * @return void
     * @magentoConfigFixture current_store web/session/use_frontend_sid 1
     */
    public function testNoSid(): void
    {
        $this->_customerSession->authenticate();
        $location = (string)$this->response->getHeader('Location');
        $this->assertNotEmpty($location);
        $this->assertStringNotContainsString(SidResolverInterface::SESSION_ID_QUERY_PARAM .'=', $location);
        $beforeAuthUrl = $this->_customerSession->getData('before_auth_url');
        $this->assertNotEmpty($beforeAuthUrl);
        $this->assertStringNotContainsString(SidResolverInterface::SESSION_ID_QUERY_PARAM .'=', $beforeAuthUrl);

        $this->_customerSession->authenticate('/customer/account');
        $location = (string)$this->response->getHeader('Location');
        $this->assertNotEmpty($location);
        $this->assertStringNotContainsString(SidResolverInterface::SESSION_ID_QUERY_PARAM .'=', $location);
    }
}
