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
use Throwable;

/**
 * Process one time token and build redirect url
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HashProcessor implements StoreSwitcherInterface
{
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
        private readonly RequestInterface $request,
        private readonly RedirectDataPostprocessorInterface $postprocessor,
        private readonly RedirectDataSerializerInterface $dataSerializer,
        private readonly ManagerInterface $messageManager,
        private readonly ContextInterfaceFactory $contextFactory,
        private readonly RedirectDataInterfaceFactory $dataFactory,
        private readonly RedirectDataValidator $dataValidator,
        private readonly LoggerInterface $logger
    ) {
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
        if ($this->request->getParam('data') !== null) {
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
            } catch (Throwable $exception) {
                $this->logger->error($exception);
                $this->messageManager->addErrorMessage(
                    __('Something went wrong.')
                );
            }
        }

        return $redirectUrl;
    }
}
