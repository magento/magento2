<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer;

/**
 * Integration Name Renderer
 */
class Name extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * Render integration name.
     *
     * If integration endpoint URL is unsecure then add error message to integration name.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /** @var \Magento\Integration\Model\Integration $row */
        $text = parent::render($row);
        if (!$this->isUrlSecure($row->getEndpoint()) || !$this->isUrlSecure($row->getIdentityLinkUrl())) {
            $text .= '<span class="security-notice"><span>' . __("Integration not secure") . '</span></span>';
        }
        return $text;
    }

    /**
     * Check if URL is secure.
     *
     * @param string $url
     * @return bool
     */
    protected function isUrlSecure($url)
    {
        return (strpos($url, 'http:') !== 0);
    }
}
