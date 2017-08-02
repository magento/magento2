<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Create order comment form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Comment extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Data Form object
     *
     * @var \Magento\Framework\Data\Form
     * @since 2.0.0
     */
    protected $_form;

    /**
     * Get header css class
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderCssClass()
    {
        return 'head-comment';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Order Comment');
    }

    /**
     * Get comment note
     *
     * @return string
     * @since 2.0.0
     */
    public function getCommentNote()
    {
        return $this->escapeHtml($this->getQuote()->getCustomerNote());
    }
}
