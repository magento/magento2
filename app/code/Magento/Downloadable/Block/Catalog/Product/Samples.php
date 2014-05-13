<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Block\Catalog\Product;

use Magento\Downloadable\Model\Resource\Sample;

/**
 * Downloadable Product Samples part block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Samples extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Enter description here...
     *
     * @return boolean
     */
    public function hasSamples()
    {
        return $this->getProduct()->getTypeInstance()->hasSamples($this->getProduct());
    }

    /**
     * Get downloadable product samples
     *
     * @return array
     */
    public function getSamples()
    {
        return $this->getProduct()->getTypeInstance()->getSamples($this->getProduct());
    }

    /**
     * @param Sample $sample
     * @return string
     */
    public function getSampleUrl($sample)
    {
        return $this->getUrl('downloadable/download/sample', array('sample_id' => $sample->getId()));
    }

    /**
     * Return title of samples section
     *
     * @return string
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
     */
    public function getIsOpenInNewWindow()
    {
        return $this->_scopeConfig->isSetFlag(\Magento\Downloadable\Model\Link::XML_PATH_TARGET_NEW_WINDOW, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
