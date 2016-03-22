<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * Backend abstract block
 */
class AbstractBlock extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }
}
