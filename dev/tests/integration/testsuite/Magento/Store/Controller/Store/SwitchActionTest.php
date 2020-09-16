<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Controller\Store;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\Interception\InterceptorInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterfaceFactory;
use Magento\Store\Model\StoreSwitcher\RedirectDataGenerator;
use Magento\Store\Model\StoreSwitcher\RedirectDataPostprocessorInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorInterface;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for store switch controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 */
class SwitchActionTest extends AbstractController
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
     * @var RedirectDataPostprocessorInterface
     */
    private $postprocessor;
    /**
     * @var MockObject
     */
    private $postprocessorMock;
    /**
     * @var UserContextInterface
     */
    private $userContext;
    /**
     * @var MockObject
     */
    private $userContextMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->preprocessor = $this->_objectManager->get(RedirectDataPreprocessorInterface::class);
        $this->preprocessorMock = $this->createMock(RedirectDataPreprocessorInterface::class);
        $this->_objectManager->addSharedInstance($this->preprocessorMock, $this->getClassName($this->preprocessor));

        $this->postprocessor = $this->_objectManager->get(RedirectDataPostprocessorInterface::class);
        $this->postprocessorMock = $this->createMock(RedirectDataPostprocessorInterface::class);
        $this->_objectManager->addSharedInstance($this->postprocessorMock, $this->getClassName($this->postprocessor));

        $this->userContext = $this->_objectManager->get(UserContextInterface::class);
        $this->userContextMock = $this->createMock(UserContextInterface::class);
        $this->_objectManager->addSharedInstance($this->userContextMock, $this->getClassName($this->userContext));
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        if ($this->preprocessor) {
            $this->_objectManager->addSharedInstance($this->preprocessor, $this->getClassName($this->preprocessor));
        }
        if ($this->postprocessor) {
            $this->_objectManager->addSharedInstance($this->postprocessor, $this->getClassName($this->postprocessor));
        }
        if ($this->userContext) {
            $this->_objectManager->addSharedInstance($this->userContext, $this->getClassName($this->userContext));
        }
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture web/url/use_store 0
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_link_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/secure/base_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/secure/base_link_url http://second_store.test/
     */
    public function testSwitch()
    {
        $data = ['key1' => 'value1', 'key2' => 1];
        $this->preprocessorMock->method('process')
            ->willReturn($data);
        $this->userContextMock->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);
        $this->userContextMock->method('getUserId')
            ->willReturn(1);
        $this->postprocessorMock->expects($this->once())
            ->method('process')
            ->with(
                $this->callback(
                    function (ContextInterface $context) {
                        return $context->getFromStore()->getCode() === 'fixture_second_store'
                            && $context->getTargetStore()->getCode() === 'default'
                            && $context->getRedirectUrl() === 'http://localhost/index.php/'
                            && $context->getCustomerId() === 1;
                    }
                ),
                $data
            );
        $redirectDataGenerator = $this->_objectManager->get(RedirectDataGenerator::class);
        $contextFactory = $this->_objectManager->get(ContextInterfaceFactory::class);
        $storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $urlEncoder = $this->_objectManager->get(UrlCoder::class);
        $fromStore = $storeManager->getStore('fixture_second_store');
        $targetStore = $storeManager->getStore('default');
        $redirectData = $redirectDataGenerator->generate(
            $contextFactory->create(
                [
                    'fromStore' => $fromStore,
                    'targetStore' => $targetStore,
                    'redirectUrl' => '/',
                ]
            )
        );
        $this->getRequest()->setParams(
            [
                '___from_store' => $fromStore->getCode(),
                StoreResolverInterface::PARAM_NAME => $targetStore->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $urlEncoder->encode('/'),
                'data' => $redirectData->getData(),
                'time_stamp' => $redirectData->getTimestamp(),
                'signature' => $redirectData->getSignature(),
            ]
        );
        $this->dispatch('stores/store/switch');
        $this->assertRedirect($this->equalTo('http://localhost/index.php/'));
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
     * Ensure that proper default store code is calculated.
     *
     * Make sure that if default store code is changed from 'default' to something else,
     * proper code is used in HTTP context. If default store code is still 'default' this may lead to
     * incorrect work of page cache.
     *
     * @magentoDbIsolation enabled
     */
    public function testExecuteWithCustomDefaultStore()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();
        $defaultStoreCode = 'default';
        $modifiedDefaultCode = 'modified_default_code';
        $this->changeStoreCode($defaultStoreCode, $modifiedDefaultCode);

        $this->dispatch('stores/store/switch');
        /** @var \Magento\Framework\App\Http\Context $httpContext */
        $httpContext = $this->_objectManager->get(\Magento\Framework\App\Http\Context::class);
        $httpContext->unsValue(\Magento\Store\Model\Store::ENTITY);
        $this->assertEquals($modifiedDefaultCode, $httpContext->getValue(\Magento\Store\Model\Store::ENTITY));

        $this->changeStoreCode($modifiedDefaultCode, $defaultStoreCode);
    }

    /**
     * Change store code.
     *
     * @param string $from
     * @param string $to
     */
    private function changeStoreCode($from, $to)
    {
        /** @var Store $store */
        $store = $this->_objectManager->create(Store::class);
        $store->load($from, 'code');
        $store->setCode($to);
        $store->save();
    }
}
