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
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 */
namespace Magento\Rss\Block\Order;

class Status extends \Magento\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $_rssFactory;

    /**
     * @var \Magento\Rss\Model\Resource\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\Rss\Model\Resource\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\Rss\Model\Resource\OrderFactory $orderFactory,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_rssFactory = $rssFactory;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey('rss_order_status_' . $this->getRequest()->getParam('data'));
        $this->setCacheLifetime(600);
    }

    protected function _toHtml()
    {
        /** @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $this->_rssFactory->create();
        $order = $this->_coreRegistry->registry('current_order');
        if (!$order) {
            return '';
        }
        $title = __('Order # %1 Notification(s)', $order->getIncrementId());
        $newUrl = $this->_urlBuilder->getUrl('sales/order/view', array('order_id' => $order->getId()));
        $rssObj->_addHeader(array(
            'title'       => $title,
            'description' => $title,
            'link'        => $newUrl,
            'charset'     => 'UTF-8',
        ));
        /** @var $resourceModel \Magento\Rss\Model\Resource\Order */
        $resourceModel = $this->_orderFactory->create();
        $results = $resourceModel->getAllCommentCollection($order->getId());
        if ($results) {
            foreach ($results as $result) {
                $urlAppend = 'view';
                $type = $result['entity_type_code'];
                if ($type && $type != 'order') {
                   $urlAppend = $type;
                }
                $type  = __(ucwords($type));
                $title = __('Details for %1 #%2', $type, $result['increment_id']);
                $description = '<p>'
                    . __('Notified Date: %1<br/>',$this->formatDate($result['created_at']))
                    . __('Comment: %1<br/>',$result['comment'])
                    . '</p>';
                $url = $this->_urlBuilder->getUrl('sales/order/' . $urlAppend, array('order_id' => $order->getId()));
                $rssObj->_addEntry(array(
                    'title'         => $title,
                    'link'          => $url,
                    'description'   => $description,
                ));
            }
        }
        $title = __('Order #%1 created at %2', $order->getIncrementId(), $this->formatDate($order->getCreatedAt()));
        $url = $this->_urlBuilder->getUrl('sales/order/view',array('order_id' => $order->getId()));
        $description = '<p>'
            . __('Current Status: %1<br/>', $order->getStatusLabel())
            . __('Total: %1<br/>', $order->formatPrice($order->getGrandTotal()))
            . '</p>';
        $rssObj->_addEntry(array(
            'title'         => $title,
            'link'          => $url,
            'description'   => $description,
        ));
        return $rssObj->createRssXml();
    }
}
