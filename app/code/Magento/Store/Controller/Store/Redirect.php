<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Controller\Store;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\Generic;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreResolver;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Store\Model\StoreSwitcher\HashGenerator;

/**
 * Builds correct url to target store (group) and performs redirect.
 */
class Redirect extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreResolverInterface $storeResolver
     * @param Generic $session
     * @param SidResolverInterface $sidResolver
     * @param HashGenerator $hashGenerator
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        StoreResolverInterface $storeResolver,
        Generic $session,
        SidResolverInterface $sidResolver,
        HashGenerator $hashGenerator
    ) {
        parent::__construct($context);
        $this->storeRepository = $storeRepository;
        $this->storeResolver = $storeResolver;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @inheritDoc
     *
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var Store $currentStore */
        $currentStore = $this->storeRepository->getById($this->storeResolver->getCurrentStoreId());
        $targetStoreCode = $this->_request->getParam(StoreResolver::PARAM_NAME);
        $fromStoreCode = $this->_request->getParam('___from_store');
        $error = null;

        if ($targetStoreCode === null) {
            return $this->_redirect($currentStore->getBaseUrl());
        }

        try {
            /** @var Store $fromStore */
            $fromStore = $this->storeRepository->get($fromStoreCode);
        } catch (NoSuchEntityException $e) {
            $error = __('Requested store is not found');
        }

        if ($error !== null) {
            $this->messageManager->addErrorMessage($error);
            $this->_redirect->redirect($this->_response, $currentStore->getBaseUrl());
        } else {
            $encodedUrl = $this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED);

            $query = [
                '___from_store' => $fromStore->getCode(),
                StoreResolverInterface::PARAM_NAME => $targetStoreCode,
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl,
            ];

            $customerHash = $this->hashGenerator->generateHash($fromStore);
            $query = array_merge($query, $customerHash);

            $arguments = [
                '_nosid' => true,
                '_query' => $query
            ];
            $this->_redirect->redirect($this->_response, 'stores/store/switch', $arguments);
        }
        // phpstan:ignore "Method Magento\Store\Controller\Store\Redirect::execute() should return *"
    }
}
