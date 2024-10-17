<?php

namespace Magento\GraphQl\Plugin\View\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class RepositoryPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Plugin before method getUrlWithParams
     *
     * @param  Magento\Framework\View\Asset\Repository
     * @param  string $fileId
     * @param  array $params
     * @return null|array
     */
    public function beforeGetUrlWithParams(
        \Magento\Framework\View\Asset\Repository $subject,
        $fileId,
        array $params
    ) {
        if (!isset($params['themeId'])) {
            $themeId = $this->scopeConfig->getValue(
                \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );

            $params['themeId'] = $themeId;
        }

        return [$fileId, $params];
    }
}