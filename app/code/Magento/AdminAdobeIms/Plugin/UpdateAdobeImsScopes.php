<?php

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

class UpdateAdobeImsScopes
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ImsConfig $imsConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ImsConfig $imsConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->imsConfig = $imsConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Update scopes for adobe_ims when admin_adobe_ims is enabled
     *
     * @param ImsConfig $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetScopes(ImsConfig $subject, string $result): string
    {
        if ($this->imsConfig->enabled() === false) {
            return $result;
        }

        return $this->getAdminAdobeImsScopes();
    }

    /**
     * Get scopes for AdminAdobeIms
     *
     * @return string
     */
    private function getAdminAdobeImsScopes(): string
    {
        return implode(
            ',',
            $this->scopeConfig->getValue(ImsConfig::XML_PATH_ADMIN_ADOBE_IMS_SCOPES)
        );
    }
}
