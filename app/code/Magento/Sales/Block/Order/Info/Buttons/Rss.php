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
namespace Magento\Sales\Block\Order\Info\Buttons;

/**
 * Block of links in Order view page
 */
class Rss extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'order/info/buttons/rss.phtml';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        array $data = array()
    ) {
        $this->orderFactory = $orderFactory;
        $this->rssUrlBuilder = $rssUrlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->rssUrlBuilder->getUrl($this->getLinkParams());
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Subscribe to Order Status');
    }

    /**
     * Check whether status notification is allowed
     *
     * @return bool
     */
    public function isRssAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/order/status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve order status url key
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getUrlKey($order)
    {
        $data = array(
            'order_id' => $order->getId(),
            'increment_id' => $order->getIncrementId(),
            'customer_id' => $order->getCustomerId()
        );
        return base64_encode(json_encode($data));
    }

    /**
     * @return string
     */
    protected function getLinkParams()
    {
        $order = $this->orderFactory->create()->load($this->_request->getParam('order_id'));
        return array(
            'type' => 'order_status',
            '_secure' => true,
            '_query' => array('data' => $this->getUrlKey($order))
        );
    }
}
