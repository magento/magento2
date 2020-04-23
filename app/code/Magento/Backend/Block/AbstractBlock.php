<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * Base for all admin blocks.
 *
 * Avoid using this class. Will be deprecated
 *
 * Marked as public API because it is actively used now.
 * @api
 * @since 100.0.2
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
        $this->_authorization = $context->getAuthorization();
        parent::__construct($context, $data);
    }
}
