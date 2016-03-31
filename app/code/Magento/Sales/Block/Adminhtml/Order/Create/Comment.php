<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Create order comment form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Comment extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Data Form object
     *
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * Get header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-comment';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Order Comment');
    }

    /**
     * Get comment note
     *
     * @return string
     */
    public function getCommentNote()
    {
        return $this->escapeHtml($this->getQuote()->getCustomerNote());
    }
}
