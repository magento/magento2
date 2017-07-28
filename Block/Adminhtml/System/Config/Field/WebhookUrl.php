<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Input field transformed to text node with link to store Signifyd webhooks controller.
 * @since 2.2.0
 */
class WebhookUrl extends Field
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $url = '';
        $originalData = $element->getOriginalData();
        if (!empty($originalData['handler_url'])) {
            $url = $this->getStoreUrl();
            $url .= $originalData['handler_url'];
        }

        return '<p class="webhook-url">' . $this->escapeHtml($url) . '</p>';
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function _isInheritCheckboxRequired(AbstractElement $element)
    {
        return false;
    }

    /**
     * Return base store URL.
     *
     * @return string
     * @since 2.2.0
     */
    private function getStoreUrl()
    {
        $website = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'));

        $isSecure = $this->_scopeConfig->isSetFlag(
            Store::XML_PATH_SECURE_IN_FRONTEND,
            ScopeInterface::SCOPE_WEBSITE,
            $website->getCode()
        );

        $configPath = $isSecure ? Store::XML_PATH_SECURE_BASE_LINK_URL : Store::XML_PATH_UNSECURE_BASE_LINK_URL;

        return $this->_scopeConfig->getValue($configPath, ScopeInterface::SCOPE_WEBSITE, $website->getCode());
    }
}
