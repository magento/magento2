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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml shipping UPS content block
 */
namespace Magento\Backend\Block\System\Shipping;

class Ups extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Usa\Model\Shipping\Carrier\Ups
     */
    protected $_shippingModel;

    /**
     * @var \Magento\Core\Model\Website
     */
    protected $_websiteModel;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Usa\Model\Shipping\Carrier\Ups $shippingModel
     * @param \Magento\Core\Model\Website $websiteModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Usa\Model\Shipping\Carrier\Ups $shippingModel,
        \Magento\Core\Model\Website $websiteModel,
        array $data = array()
    ) {
        $this->_shippingModel = $shippingModel;
        $this->_websiteModel = $websiteModel;
        parent::__construct($context, $data);
    }

    /**
     * Get shipping model
     *
     * @return \Magento\Usa\Model\Shipping\Carrier\Ups
     */
    public function getShippingModel()
    {
        return $this->_shippingModel;
    }

    /**
     * Get website model
     *
     * @return \Magento\Core\Model\Website
     */
    public function getWebsiteModel()
    {
        return $this->_websiteModel;
    }

    /**
     * Get store config
     *
     * @param string $path
     * @param mixed $store
     * @return mixed
     */
    public function getConfig($path, $store = null)
    {
        return $this->_storeConfig->getConfig($path, $store);
    }
}
