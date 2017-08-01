<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Grid\Row;

/**
 * Sales orders grid row url generator
 * @since 2.0.0
 */
class UrlGenerator extends \Magento\Backend\Model\Widget\Grid\Row\UrlGenerator
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     * @since 2.0.0
     */
    protected $_authorization;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param array $args
     * @since 2.0.0
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
     *
     * @param \Magento\Framework\DataObject $item
     * @return bool|string
     * @since 2.0.0
     */
    public function getUrl($item)
    {
        if ($this->_authorization->isAllowed('Magento_Sales::sales_invoice')) {
            return parent::getUrl($item);
        }
        return false;
    }
}
