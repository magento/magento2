<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\ViewModel;

use Magento\AdobeImsApi\Api\AuthorizationInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class LinkViewModel implements ArgumentInterface
{
    /**
     * @var string|null
     */
    private ?string $authUrl;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var MessageManagerInterface
     */
    private MessageManagerInterface $messageManager;

    /**
     * @param AuthorizationInterface $authorization
     * @param LoggerInterface $logger
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        AuthorizationInterface  $authorization,
        LoggerInterface $logger,
        MessageManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->messageManager = $messageManager;

        try {
            $this->authUrl = $authorization->getAuthUrl();
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            $this->authUrl = null;
            $this->addImsErrorMessage(
                'Could not connect to Adobe IMS.',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->authUrl = null;
            $this->addImsErrorMessage(
                'Could not connect to Adobe IMS.',
                'Something went wrong during Adobe IMS connection check.'
            );
        }
    }

    /**
     * Check if authorization Url is not empty
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->authUrl !== '';
    }

    /**
     * Get authorization URL for Login Button
     *
     * @return string|null
     */
    public function getButtonLink(): ?string
    {
        return $this->authUrl;
    }

    /**
     * Add Admin Adobe IMS Error Message
     *
     * @param string $headline
     * @param string $message
     * @return void
     */
    private function addImsErrorMessage(string $headline, string $message): void
    {
        $this->messageManager->addComplexErrorMessage(
            'adminAdobeImsMessage',
            [
                'headline' => __($headline)->getText(),
                'message' => __($message)->getText()
            ]
        );
    }
}
