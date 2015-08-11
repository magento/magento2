<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Block\Transparent;

use Magento\Payment\Block\Transparent\Iframe as TransparentIframe;

/**
 * Class Iframe
 */
class Iframe extends TransparentIframe
{
    /**
     * @var \Magento\Authorizenet\Helper\DataFactory
     */
    protected $dataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Authorizenet\Helper\DataFactory $dataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Authorizenet\Helper\DataFactory $dataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->dataFactory = $dataFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Get helper data
     *
     * @param string $area
     * @return \Magento\Authorizenet\Helper\Backend\Data|\Magento\Authorizenet\Helper\Data
     */
    public function getHelper($area)
    {
        return $this->dataFactory->create($area);
    }

    /**
     * Escape redirect url and add success message
     *
     * @param string $url
     * @return string
     */
    public function getRedirectParentUrl($url)
    {
        $this->messageManager->addSuccess(__('You created the order.'));
        return $this->escapeUrl($url);
    }
}
