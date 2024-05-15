<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Controller\Store;

use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataSerializerInterface;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test Redirect controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 */
class RedirectTest extends AbstractController
{
    /**
     * @var RedirectDataPreprocessorInterface
     */
    private $preprocessor;
    /**
     * @var MockObject
     */
    private $preprocessorMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->preprocessor = $this->_objectManager->get(RedirectDataPreprocessorInterface::class);
        $this->preprocessorMock = $this->createMock(RedirectDataPreprocessorInterface::class);
        $this->_objectManager->addSharedInstance($this->preprocessorMock, $this->getClassName($this->preprocessor));
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        if ($this->preprocessor) {
            $this->_objectManager->addSharedInstance($this->preprocessor, $this->getClassName($this->preprocessor));
        }
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture web/url/use_store 0
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_link_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/secure/base_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/secure/base_link_url http://second_store.test/
     */
    public function testRedirectToSecondStoreOnAnotherUrl(): void
    {
        $data = ['key1' => 'value1', 'key2' => 1];
        $this->preprocessorMock->method('process')
            ->willReturn($data);
        $this->getRequest()->setParam(StoreResolver::PARAM_NAME, 'fixture_second_store');
        $this->getRequest()->setParam('___from_store', 'default');
        $this->dispatch('/stores/store/redirect');
        $header = $this->getResponse()->getHeader('Location');
        $this->assertNotEmpty($header);
        $result = $header->getFieldValue();
        $this->assertStringStartsWith('http://second_store.test/', $result);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $urlParts = parse_url($result);
        $this->assertStringEndsWith('stores/store/switch/', $urlParts['path']);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        parse_str($urlParts['query'], $params);
        $this->assertTrue(!empty($params['time_stamp']));
        $this->assertTrue(!empty($params['signature']));
        $this->assertTrue(!empty($params['data']));
        $serializer = $this->_objectManager->get(RedirectDataSerializerInterface::class);
        $this->assertEquals($data, $serializer->unserialize($params['data']));
    }

    /**
     * Return class name of the given object
     *
     * @param mixed $instance
     */
    private function getClassName($instance): string
    {
        if ($instance instanceof InterceptorInterface) {
            $actionClass = get_parent_class($instance);
        } else {
            $actionClass = get_class($instance);
        }
        return $actionClass;
    }

    /**
     * Check that there's no SID in redirect URL.
     *
     * @return void
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture current_store web/session/use_frontend_sid 1
     */
    public function testNoSid(): void
    {
        $this->getRequest()->setParam(StoreResolver::PARAM_NAME, 'fixture_second_store');
        $this->getRequest()->setParam('___from_store', 'test');

        $this->dispatch('/stores/store/redirect');

        $result = (string)$this->getResponse()->getHeader('location');
        $this->assertNotEmpty($result);
        $this->assertStringNotContainsString(SidResolverInterface::SESSION_ID_QUERY_PARAM . '=', $result);
    }
}
