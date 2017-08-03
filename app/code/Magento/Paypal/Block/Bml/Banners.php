<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Bml;

use Magento\Framework\View\Element\Template;
use Magento\Paypal\Model\Config;

/**
 * @api
 * @since 2.0.0
 */
class Banners extends Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_section;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $_position;

    /**
     * @var \Magento\Paypal\Model\Config
     * @since 2.0.0
     */
    protected $_paypalConfig;

    /**
     * @param Template\Context $context
     * @param Config $paypalConfig
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        Config $paypalConfig,
        array $data = []
    ) {
        $this->_section = isset($data['section']) ? (string)$data['section'] : '';
        $this->_position = isset($data['position']) ? (int)$data['position'] : 0;
        $this->_paypalConfig = $paypalConfig;
        parent::__construct($context, $data);
    }

    /**
     * Disable block output if banner turned off or PublisherId is miss
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->_paypalConfig->isMethodAvailable(Config::METHOD_WPP_BML)
            && !$this->_paypalConfig->isMethodAvailable(Config::METHOD_WPP_PE_BML)) {
            return '';
        }
        $publisherId = $this->_paypalConfig->getBmlPublisherId();
        $display = $this->_paypalConfig->getBmlDisplay($this->_section);
        $position = $this->_paypalConfig->getBmlPosition($this->_section);
        if (!$publisherId || $display == 0 || $this->_position != $position) {
            return '';
        }
        $this->setData('publisher_id', $publisherId);
        $this->setData('size', $this->_paypalConfig->getBmlSize($this->_section));
        return parent::_toHtml();
    }
}
