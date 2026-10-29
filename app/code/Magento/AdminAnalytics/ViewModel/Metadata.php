<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AdminAnalytics\ViewModel;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Csp\Helper\CspNonceProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\State;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Information;

/**
 * Gets user version and mode
 */
class Metadata implements ArgumentInterface
{
    /**
     * @var string
     */
    private $nonce;

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
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var CspNonceProvider
     */
    private $nonceProvider;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param Session $authSession
     * @param State $appState
     * @param ScopeConfigInterface $config
     * @param CspNonceProvider|null $nonceProvider
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        Session $authSession,
        State $appState,
        ScopeConfigInterface $config,
        ?CspNonceProvider $nonceProvider = null
    ) {
        $this->productMetadata = $productMetadata;
        $this->authSession = $authSession;
        $this->appState = $appState;
        $this->config = $config;

        $this->nonceProvider = $nonceProvider ?: ObjectManager::getInstance()->get(CspNonceProvider::class);

        $this->nonce = $this->nonceProvider->generateNonce();
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
     * Get product edition
     *
     * @return string
     */
    public function getProductEdition(): string
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Get current user id (hash generated from email)
     *
     * @return string
     */
    public function getCurrentUser() :string
    {
        return hash('sha256', 'ADMIN_USER' . $this->authSession->getUser()->getEmail());
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

    /**
     * Get created date for current user
     *
     * @return string
     */
    public function getCurrentUserCreatedDate(): string
    {
        return $this->authSession->getUser()->getCreated();
    }

    /**
     * Get log date for current user
     *
     * @return string|null
     */
    public function getCurrentUserLogDate(): ?string
    {
        return $this->authSession->getUser()->getLogdate();
    }

    /**
     * Get secure base URL
     *
     * @param string $scope
     * @param string|null $scopeCode
     * @return string|null
     */
    public function getSecureBaseUrlForScope(
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null
    ): ?string {
        return $this->config->getValue(Custom::XML_PATH_SECURE_BASE_URL, $scope, $scopeCode);
    }

    /**
     * Get store name
     *
     * @param string $scope
     * @param string|null $scopeCode
     * @return string|null
     */
    public function getStoreNameForScope(
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null
    ): ?string {
        return $this->config->getValue(Information::XML_PATH_STORE_INFO_NAME, $scope, $scopeCode);
    }

    /**
     * Get current user role name
     *
     * @return string
     */
    public function getCurrentUserRoleName(): string
    {
        return $this->authSession->getUser()->getRole()->getRoleName();
    }

    /**
     * Get a random nonce for each request.
     *
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }
}
