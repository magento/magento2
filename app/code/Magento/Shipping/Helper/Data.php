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

/**
 * Shipping data helper
 */
namespace Magento\Shipping\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Allowed hash keys
     *
     * @var array
     */
    protected $_allowedHashKeys = array('ship_id', 'order_id', 'track_id');

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_coreData = $coreData;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Decode url hash
     *
     * @param  string $hash
     * @return array
     */
    public function decodeTrackingHash($hash)
    {
        $hash = explode(':', $this->_coreData->urlDecode($hash));
        if (count($hash) === 3 && in_array($hash[0], $this->_allowedHashKeys)) {
            return array('key' => $hash[0], 'id' => (int)$hash[1], 'hash' => $hash[2]);
        }
        return array();
    }

    /**
     * Retrieve tracking url with params
     *
     * @param  string $key
     * @param  \Magento\Sales\Model\Order|\Magento\Sales\Model\Order\Shipment|\Magento\Sales\Model\Order\Shipment\Track $model
     * @param  string $method Optional - method of a model to get id
     * @return string
     */
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
        $urlPart = "{$key}:{$model->{$method}()}:{$model->getProtectCode()}";
        $param = array('hash' => $this->_coreData->urlEncode($urlPart));

        $storeModel = $this->_storeManager->getStore($model->getStoreId());
        return $storeModel->getUrl('shipping/tracking/popup', $param);
    }

    /**
     * Shipping tracking popup URL getter
     *
     * @param \Magento\Sales\Model\AbstractModel $model
     * @return string
     */
    public function getTrackingPopupUrlBySalesModel($model)
    {
        if ($model instanceof \Magento\Sales\Model\Order) {
            return $this->_getTrackingUrl('order_id', $model);
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment) {
            return $this->_getTrackingUrl('ship_id', $model);
        } elseif ($model instanceof \Magento\Sales\Model\Order\Shipment\Track) {
            return $this->_getTrackingUrl('track_id', $model, 'getEntityId');
        }
        return '';
    }
}
