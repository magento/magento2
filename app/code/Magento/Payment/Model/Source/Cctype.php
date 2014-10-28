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
namespace Magento\Payment\Model\Source;

/**
 * Payment CC Types Source Model
 */
class Cctype implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Allowed CC types
     *
     * @var array
     */
    protected $_allowedTypes = array();

    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * Config
     *
     * @param \Magento\Payment\Model\Config $paymentConfig
     */
    public function __construct(\Magento\Payment\Model\Config $paymentConfig)
    {
        $this->_paymentConfig = $paymentConfig;
    }

    /**
     * Return allowed cc types for current method
     *
     * @return array
     */
    public function getAllowedTypes()
    {
        return $this->_allowedTypes;
    }

    /**
     * Setter for allowed types
     *
     * @param array $values
     * @return $this
     */
    public function setAllowedTypes(array $values)
    {
        $this->_allowedTypes = $values;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /**
         * making filter by allowed cards
         */
        $allowed = $this->getAllowedTypes();
        $options = array();

        foreach ($this->_paymentConfig->getCcTypes() as $code => $name) {
            if (in_array($code, $allowed) || !count($allowed)) {
                $options[] = array('value' => $code, 'label' => $name);
            }
        }

        return $options;
    }
}
