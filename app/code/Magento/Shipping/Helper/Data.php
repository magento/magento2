<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shipping data helper
 */
namespace Magento\Shipping\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Allowed hash keys
     *
     * @var array
     */
    protected $_allowedHashKeys = ['ship_id', 'order_id', 'track_id'];

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface|null
     */
    private $url;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface|null $url
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        ?UrlInterface $url = null
    ) {
        $this->_storeManager = $storeManager;
        $this->url = $url ?: ObjectManager::getInstance()->get(UrlInterface::class);

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
        $hash = explode(':', $this->urlDecoder->decode($hash));
        if (count($hash) === 3 && in_array($hash[0], $this->_allowedHashKeys)) {
            return ['key' => $hash[0], 'id' => (int)$hash[1], 'hash' => $hash[2]];
        }
        return [];
    }

    /**
     * Retrieve tracking url with params
     *
     * @param  string $key
     * @param  \Magento\Sales\Model\Order
     * |\Magento\Sales\Model\Order\Shipment|\Magento\Sales\Model\Order\Shipment\Track $model
     * @param  string $method Optional - method of a model to get id
     * @return string
     */
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
        $urlPart = "{$key}:{$model->{$method}()}:{$model->getProtectCode()}";
        $params = [
            '_scope' => $model->getStoreId(),
            '_nosid' => true,
            '_direct' => 'shipping/tracking/popup',
            '_query' => ['hash' => $this->urlEncoder->encode($urlPart)]
        ];

        return $this->url->getUrl('', $params);
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
