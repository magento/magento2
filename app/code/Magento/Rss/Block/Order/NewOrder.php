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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 */
namespace Magento\Rss\Block\Order;

class NewOrder extends \Magento\Core\Block\AbstractBlock
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $_rssFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Core\Model\Resource\Iterator
     */
    protected $_resourceIterator;

    /**
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Core\Block\Context $context
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Core\Model\Resource\Iterator $resourceIterator
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Core\Block\Context $context,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Core\Model\Resource\Iterator $resourceIterator,
        array $data = array()
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->_rssFactory = $rssFactory;
        $this->_orderFactory = $orderFactory;
        $this->_resourceIterator = $resourceIterator;
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->_orderFactory->create();
        $passDate = $order->getResource()->formatDate(mktime(0, 0, 0, date('m'), date('d')-7));
        $newUrl = $this->_adminhtmlData->getUrl(
            'adminhtml/sales_order', array('_secure' => true, '_nosecret' => true)
        );
        $title = __('New Orders');

        /** @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $this->_rssFactory->create();
        $rssObj->_addHeader(array(
            'title'       => $title,
            'description' => $title,
            'link'        => $newUrl,
            'charset'     => 'UTF-8',
        ));

        /** @var $collection \Magento\Sales\Model\Resource\Order\Collection */
        $collection = $order->getCollection();
        $collection->addAttributeToFilter('created_at', array('date'=>true, 'from'=> $passDate))
            ->addAttributeToSort('created_at', 'desc');

        $detailBlock = $this->_layout->getBlockSingleton('Magento\Rss\Block\Order\Details');
        $this->_eventManager->dispatch('rss_order_new_collection_select', array('collection' => $collection));
        $this->_resourceIterator->walk(
            $collection->getSelect(),
            array(array($this, 'addNewOrderXmlCallback')),
            array('rssObj' => $rssObj, 'order' => $order , 'detailBlock' => $detailBlock)
        );
        return $rssObj->createRssXml();
    }

    public function addNewOrderXmlCallback($args)
    {
        /** @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $args['rssObj'];
        /** @var $order \Magento\Sales\Model\Order */
        $order = $args['order'];
        /** @var $detailBlock \Magento\Rss\Block\Order\Details */
        $detailBlock = $args['detailBlock'];
        $order->reset()->load($args['row']['entity_id']);
        if ($order && $order->getId()) {
            $title = __('Order #%1 created at %2', $order->getIncrementId(), $this->formatDate($order->getCreatedAt()));
            $url = $this->_adminhtmlData->getUrl(
                'adminhtml/sales_order/view',
                array(
                    '_secure' => true,
                    'order_id' => $order->getId(),
                    '_nosecret' => true
                )
            );
            $detailBlock->setOrder($order);
            $rssObj->_addEntry(array(
                'title'         => $title,
                'link'          => $url,
                'description'   => $detailBlock->toHtml()
            ));
        }
    }
}
