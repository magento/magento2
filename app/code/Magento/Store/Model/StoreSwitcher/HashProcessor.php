<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Psr\Log\LoggerInterface;

/**
 * Process one time token and build redirect url
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HashProcessor implements StoreSwitcherInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var RedirectDataPostprocessorInterface
     */
    private $postprocessor;
    /**
     * @var RedirectDataSerializerInterface
     */
    private $dataSerializer;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var RedirectDataInterfaceFactory
     */
    private $dataFactory;
    /**
     * @var ContextInterfaceFactory
     */
    private $contextFactory;
    /**
     * @var RedirectDataValidator
     */
    private $dataValidator;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param RedirectDataPostprocessorInterface $postprocessor
     * @param RedirectDataSerializerInterface $dataSerializer
     * @param ManagerInterface $messageManager
     * @param ContextInterfaceFactory $contextFactory
     * @param RedirectDataInterfaceFactory $dataFactory
     * @param RedirectDataValidator $dataValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        RedirectDataPostprocessorInterface $postprocessor,
        RedirectDataSerializerInterface $dataSerializer,
        ManagerInterface $messageManager,
        ContextInterfaceFactory $contextFactory,
        RedirectDataInterfaceFactory $dataFactory,
        RedirectDataValidator $dataValidator,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->postprocessor = $postprocessor;
        $this->dataSerializer = $dataSerializer;
        $this->messageManager = $messageManager;
        $this->contextFactory = $contextFactory;
        $this->dataFactory = $dataFactory;
        $this->dataValidator = $dataValidator;
        $this->logger = $logger;
    }

    /**
     * Builds redirect url with token
     *
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $timestamp = (int) $this->request->getParam('time_stamp');
        $signature = (string) $this->request->getParam('signature');
        $data = (string) $this->request->getParam('data');
        $context = $this->contextFactory->create(
            [
                'fromStore' => $fromStore,
                'targetStore' => $targetStore,
                'redirectUrl' => $redirectUrl
            ]
        );
        $redirectDataObject = $this->dataFactory->create(
            [
                'signature' => $signature,
                'timestamp' => $timestamp,
                'data' => $data
            ]
        );

        try {
            if ($redirectUrl && $this->dataValidator->validate($context, $redirectDataObject)) {
                $this->postprocessor->process($context, $this->dataSerializer->unserialize($data));
            } else {
                throw new LocalizedException(
                    __('The requested store cannot be found. Please check the request and try again.')
                );
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Throwable $exception) {
            $this->logger->error($exception);
            $this->messageManager->addErrorMessage(
                __('Something went wrong.')
            );
        }

        return $redirectUrl;
    }
}
