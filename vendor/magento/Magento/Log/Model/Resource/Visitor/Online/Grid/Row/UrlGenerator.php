<?php
/**
 * URL Generator for Customer Online Grid
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Log\Model\Resource\Visitor\Online\Grid\Row;

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
     * Create url for passed item using passed url model
     * @param \Magento\Framework\Object $item
     * @return string
     */
    public function getUrl($item)
    {
        if ($this->_authorization->isAllowed('Magento_Customer::manage') && $item->getCustomerId()) {
            return parent::getUrl($item);
        }
        return false;
    }
}
