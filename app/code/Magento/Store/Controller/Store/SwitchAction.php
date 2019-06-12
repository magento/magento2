<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Controller\Store;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher;
use Magento\Store\Model\StoreSwitcherInterface;

/**
 * Handles store switching url and makes redirect.
<<<<<<< HEAD
=======
 *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitchAction extends Action
{
    /**
     * @var StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @var HttpContext
     * @deprecated
     */
    protected $httpContext;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreManagerInterface
     * @deprecated
     */
    protected $storeManager;

    /**
     * @var StoreSwitcherInterface
     */
    private $storeSwitcher;

    /**
     * Initialize dependencies.
     *
     * @param ActionContext $context
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param StoreSwitcherInterface $storeSwitcher
     */
    public function __construct(
        ActionContext $context,
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        StoreRepositoryInterface $storeRepository,
        StoreManagerInterface $storeManager,
        StoreSwitcherInterface $storeSwitcher = null
    ) {
        parent::__construct($context);
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->messageManager = $context->getMessageManager();
<<<<<<< HEAD
        $this->storeSwitcher = $storeSwitcher ?: ObjectManager::getInstance()->get(StoreSwitcher::class);
=======
        $this->storeSwitcher = $storeSwitcher ?: ObjectManager::getInstance()->get(StoreSwitcherInterface::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Execute action
     *
     * @return void
     * @throws StoreSwitcher\CannotSwitchStoreException
     */
    public function execute()
    {
        $targetStoreCode = $this->_request->getParam(
<<<<<<< HEAD
            StoreResolver::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );
        $fromStoreCode = $this->_request->getParam('___from_store');

        $requestedUrlToRedirect = $this->_redirect->getRedirectUrl();
        $redirectUrl = $requestedUrlToRedirect;

=======
            \Magento\Store\Model\StoreManagerInterface::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );
        $fromStoreCode = $this->_request->getParam('___from_store');

        $requestedUrlToRedirect = $this->_redirect->getRedirectUrl();
        $redirectUrl = $requestedUrlToRedirect;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $error = null;
        try {
            $fromStore = $this->storeRepository->get($fromStoreCode);
            $targetStore = $this->storeRepository->getActiveStoreByCode($targetStoreCode);
        } catch (StoreIsInactiveException $e) {
            $error = __('Requested store is inactive');
        } catch (NoSuchEntityException $e) {
<<<<<<< HEAD
            $error = __('Requested store is not found');
=======
            $error = __("The store that was requested wasn't found. Verify the store and try again.");
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }
        if ($error !== null) {
            $this->messageManager->addErrorMessage($error);
        } else {
            $redirectUrl = $this->storeSwitcher->switch($fromStore, $targetStore, $requestedUrlToRedirect);
        }

        $this->getResponse()->setRedirect($redirectUrl);
    }
}
