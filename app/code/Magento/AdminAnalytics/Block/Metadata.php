<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminAnalytics\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\State;

/**
 * Gets user version and mode
 *
 * @api
 */
class Metadata extends Template
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
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param Session $authSession
     * @param State $appState
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        Session $authSession,
        State $appState,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->authSession = $authSession;
        $this->appState = $appState;
        parent::__construct($context, $data);
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
     * Get Magento mode
     *
     * @return string
     */
    public function getMode() :string
    {
        return $this->appState->getMode();
    }
}
