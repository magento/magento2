<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Downloadable\Block\Catalog\Product;

use Magento\Downloadable\Model\ResourceModel\Sample;

/**
 * Downloadable Product Samples part block
 *
 * @api
 * @since 2.0.0
 */
class Samples extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Enter description here...
     *
     * @return boolean
     * @since 2.0.0
     */
    public function hasSamples()
    {
        return $this->getProduct()->getTypeInstance()->hasSamples($this->getProduct());
    }

    /**
     * Get downloadable product samples
     *
     * @return array
     * @since 2.0.0
     */
    public function getSamples()
    {
        return $this->getProduct()->getTypeInstance()->getSamples($this->getProduct());
    }

    /**
     * @param Sample $sample
     * @return string
     * @since 2.0.0
     */
    public function getSampleUrl($sample)
    {
        return $this->getUrl('downloadable/download/sample', ['sample_id' => $sample->getId()]);
    }

    /**
     * Return title of samples section
     *
     * @return string
     * @since 2.0.0
     */
    public function getSamplesTitle()
    {
        if ($this->getProduct()->getSamplesTitle()) {
            return $this->getProduct()->getSamplesTitle();
        }
        return $this->_scopeConfig->getValue(\Magento\Downloadable\Model\Sample::XML_PATH_SAMPLES_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Return true if target of link new window
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsOpenInNewWindow()
    {
        return $this->_scopeConfig->isSetFlag(\Magento\Downloadable\Model\Link::XML_PATH_TARGET_NEW_WINDOW, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
