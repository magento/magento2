<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminAnalytics\ViewModel;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Gets user version and mode
 */
class Metadata implements ArgumentInterface
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param Session $authSession
     * @param State $appState
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        Session $authSession,
        State $appState
    ) {
        $this->productMetadata = $productMetadata;
        $this->authSession = $authSession;
        $this->appState = $appState;
    }

    /**
     * Get product version
     *
     * @return string
     */
    public function getMagentoVersion() :string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Get current user id (hash generated from email)
     *
     * @return string
     */
    public function getCurrentUser() :string
    {
        return hash('sha512', 'ADMIN_USER' . $this->authSession->getUser()->getEmail());
    }
    /**
     * Get Magento mode that the user is using
     *
     * @return string
     */
    public function getMode() :string
    {
        return $this->appState->getMode();
    }
}
