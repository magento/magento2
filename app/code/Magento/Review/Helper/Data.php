<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Review\Helper;

/**
 * Default review helper
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_REVIEW_GUETS_ALLOW = 'catalog/review/allow_guest';

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     * @since 2.0.0
     */
    protected $filter;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     * @since 2.0.0
     */
    protected $_escaper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filter
    ) {
        $this->_escaper = $escaper;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * Get review detail
     *
     * @param string $origDetail
     * @return string
     * @since 2.0.0
     */
    public function getDetail($origDetail)
    {
        return nl2br($this->filter->truncate($origDetail, ['length' => 50]));
    }

    /**
     * Return short detail info in HTML
     *
     * @param string $origDetail Full detail info
     * @return string
     * @since 2.0.0
     */
    public function getDetailHtml($origDetail)
    {
        return nl2br($this->filter->truncate($this->_escaper->escapeHtml($origDetail), ['length' => 50]));
    }

    /**
     * Return an indicator of whether or not guest is allowed to write
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsGuestAllowToWrite()
    {
        return $this->scopeConfig->isSetFlag(self::XML_REVIEW_GUETS_ALLOW, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get review statuses with their codes
     *
     * @return array
     * @since 2.0.0
     */
    public function getReviewStatuses()
    {
        return [
            \Magento\Review\Model\Review::STATUS_APPROVED => __('Approved'),
            \Magento\Review\Model\Review::STATUS_PENDING => __('Pending'),
            \Magento\Review\Model\Review::STATUS_NOT_APPROVED => __('Not Approved')
        ];
    }

    /**
     * Get review statuses as option array
     *
     * @return array
     * @since 2.0.0
     */
    public function getReviewStatusesOptionArray()
    {
        $result = [];
        foreach ($this->getReviewStatuses() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }
}
