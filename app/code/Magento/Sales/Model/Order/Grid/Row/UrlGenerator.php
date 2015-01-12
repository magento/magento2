<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Grid\Row;

/**
 * Sales orders grid row url generator
 */
class UrlGenerator extends \Magento\Backend\Model\Widget\Grid\Row\UrlGenerator
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param array $args
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $args = []
    ) {
        $this->_authorization = $authorization;
        parent::__construct($backendUrl, $args);
    }

    /**
     * Generate row url
     * @param \Magento\Framework\Object $item
     * @return bool|string
     */
    public function getUrl($item)
    {
        if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
            return parent::getUrl($item);
        }
        return false;
    }
}
