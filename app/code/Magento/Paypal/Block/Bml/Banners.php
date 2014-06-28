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

namespace Magento\Paypal\Block\Bml;

use Magento\Framework\View\Element\Template;
use Magento\Paypal\Model\Config;

class Banners extends Template
{
    /**
     * @var string
     */
    protected $_section;

    /**
     * @var int
     */
    protected $_position;

    /**
     * @var \Magento\Paypal\Model\Config
     */
    protected $_paypalConfig;

    /**
     * @param Template\Context $context
     * @param Config $paypalConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $paypalConfig,
        array $data = array()
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
     */
    protected function _toHtml()
    {
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
