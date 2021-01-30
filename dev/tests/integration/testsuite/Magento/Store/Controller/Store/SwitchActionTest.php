<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Controller\Store;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Response\RedirectInterface;
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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Model\ResourceModel\Store as StoreResource;

/**
 * Test for store switch controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 */
class SwitchActionTest extends AbstractController
{
    /** @var RedirectDataPreprocessorInterface */
    private $preprocessor;

    /** @var MockObject */
    private $preprocessorMock;

    /** @var RedirectDataPostprocessorInterface */
    private $postprocessor;

    /** @var MockObject */
    private $postprocessorMock;

    /** @var RedirectDataGenerator */
    private $redirectDataGenerator;

    /** @var ContextInterfaceFactory */
    private $contextFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var UrlCoder */
    private $urlEncoder;

    /** @var RedirectInterface */
    private $redirect;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreResource */
    private $storeResource;

    /** @var StoreInterfaceFactory */
    private $storeFactory;

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
        $this->redirectDataGenerator = $this->_objectManager->get(RedirectDataGenerator::class);
        $this->contextFactory = $this->_objectManager->get(ContextInterfaceFactory::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->urlEncoder = $this->_objectManager->get(UrlCoder::class);
        $this->redirect = $this->_objectManager->get(RedirectInterface::class);
        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->storeResource = $this->_objectManager->get(StoreResource::class);
        $this->storeFactory = $this->_objectManager->get(StoreInterfaceFactory::class);
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
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture web/url/use_store 0
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/unsecure/base_link_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/secure/base_url http://second_store.test/
     * @magentoConfigFixture fixture_second_store_store web/secure/base_link_url http://second_store.test/
     * @return void
     */
    public function testSwitch(): void
    {
        $data = ['key1' => 'value1', 'key2' => 1];
        $this->preprocessorMock->method('process')
            ->willReturn($data);
        $this->postprocessorMock->expects($this->once())
            ->method('process')
            ->with(
                $this->callback(
                    function (ContextInterface $context) {
                        return $context->getFromStore()->getCode() === 'fixture_second_store'
                            && $context->getTargetStore()->getCode() === 'default'
                            && $context->getRedirectUrl() === 'http://localhost/index.php/';
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
     * @return string
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
     * @return void
     */
    public function testExecuteWithCustomDefaultStore(): void
    {
        Bootstrap::getInstance()->reinitialize();
        $defaultStoreCode = 'default';
        $modifiedDefaultCode = 'modified_default_code';
        $this->changeStoreCode($defaultStoreCode, $modifiedDefaultCode);

        $this->dispatch('stores/store/switch');
        /** @var Context $httpContext */
        $httpContext = $this->_objectManager->get(Context::class);
        $httpContext->unsValue(Store::ENTITY);
        $this->assertEquals($modifiedDefaultCode, $httpContext->getValue(Store::ENTITY));

        $this->changeStoreCode($modifiedDefaultCode, $defaultStoreCode);
    }

    /**
     * Change store code.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    private function changeStoreCode(string $from, string $to): void
    {
        /** @var Store $store */
        $store = $this->storeFactory->create();
        $this->storeResource->load($store, $from, 'code');
        $store->setCode($to);
        $this->storeResource->save($store);
    }

    /**
     * Switch to category on second store
     *
     * @magentoDataFixture Magento/Catalog/_files/category_on_second_store.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testSwitchToCategoryOnSecondStore(): void
    {
        $id = 333;
        $fromStore = $this->storeManager->getStore();
        $targetStore = $this->storeManager->getStore('test');
        $category = $this->categoryRepository->get($id, $fromStore->getId());

        $redirectData = $this->redirectDataGenerator->generate(
            $this->contextFactory->create(
                [
                    'fromStore' => $fromStore,
                    'targetStore' => $targetStore,
                    'redirectUrl' => $this->redirect->getRedirectUrl(),
                ]
            )
        );

        $this->getRequest()->setParams(
            [
                '___from_store' => $fromStore->getCode(),
                StoreManagerInterface::PARAM_NAME => $targetStore->getCode(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($category->getUrl()),
                'data' => $redirectData->getData(),
                'time_stamp' => $redirectData->getTimestamp(),
                'signature' => $redirectData->getSignature(),
            ]
        );

        $this->dispatch('stores/store/switch');
        $categorySecond = $this->categoryRepository->get($id, $targetStore->getId());
        $this->assertRedirect($this->stringContains($categorySecond->getUrlKey()));
    }
}
