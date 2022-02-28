<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\ViewModel;

use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class LinkViewModel implements ArgumentInterface
{
    /** @var string */
    private string $authUrl;

    /**
     * @param ImsConnection $connection
     */
    public function __construct(
        ImsConnection $connection
    ) {
        try {
            $this->authUrl = $connection->auth();
        } catch (InvalidArgumentException $exception) {
            $this->authUrl = '';
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
     * @return string
     */
    public function getButtonLink(): string
    {
        return $this->authUrl;
    }
}
