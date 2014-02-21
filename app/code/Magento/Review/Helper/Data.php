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
 * @category    Magento
 * @package     Magento_Review
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Review\Helper;

/**
 * Default review helper
 */
class Data extends \Magento\App\Helper\AbstractHelper
{
    const XML_REVIEW_GUETS_ALLOW = 'catalog/review/allow_guest';

    /**
     * Filter manager
     *
     * @var \Magento\Filter\FilterManager
     */
    protected $filter;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Escaper
     *
     * @var \Magento\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Escaper $escaper
     * @param \Magento\Filter\FilterManager $filter
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Escaper $escaper,
        \Magento\Filter\FilterManager $filter
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_escaper = $escaper;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * Get review detail
     *
     * @param string $origDetail
     * @return string
     */
    public function getDetail($origDetail)
    {
        return nl2br($this->filter->truncate($origDetail, array('length' => 50)));
    }

    /**
     * Return short detail info in HTML
     *
     * @param string $origDetail Full detail info
     * @return string
     */
    public function getDetailHtml($origDetail)
    {
        return nl2br($this->filter->truncate($this->_escaper->escapeHtml($origDetail), array('length' => 50)));
    }

    /**
     * Return an indicator of whether or not guest is allowed to write
     *
     * @return bool
     */
    public function getIsGuestAllowToWrite()
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_REVIEW_GUETS_ALLOW);
    }

    /**
     * Get review statuses with their codes
     *
     * @return array
     */
    public function getReviewStatuses()
    {
        return array(
            \Magento\Review\Model\Review::STATUS_APPROVED     => __('Approved'),
            \Magento\Review\Model\Review::STATUS_PENDING      => __('Pending'),
            \Magento\Review\Model\Review::STATUS_NOT_APPROVED => __('Not Approved'),
        );
    }

    /**
     * Get review statuses as option array
     *
     * @return array
     */
    public function getReviewStatusesOptionArray()
    {
        $result = array();
        foreach ($this->getReviewStatuses() as $value => $label) {
            $result[] = array('value' => $value, 'label' => $label);
        }

        return $result;
    }
}
