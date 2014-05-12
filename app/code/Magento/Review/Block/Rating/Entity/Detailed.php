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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Review\Block\Rating\Entity;

/**
 * Entity rating block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Detailed extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'detailed.phtml';

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        array $data = array()
    ) {
        $this->_ratingFactory = $ratingFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $entityId = $this->_request->getParam('id');
        if (intval($entityId) <= 0) {
            return '';
        }

        $reviewsCount = $this->_ratingFactory->create()->getTotalReviews($entityId, true);
        if ($reviewsCount == 0) {
            #return __('Be the first to review this product');
            $this->setTemplate('empty.phtml');
            return parent::_toHtml();
        }

        $ratingCollection = $this->_ratingFactory->create()->getResourceCollection()->addEntityFilter(
            'product' # TOFIX
        )->setPositionOrder()->setStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addRatingPerStoreName(
            $this->_storeManager->getStore()->getId()
        )->load();

        if ($entityId) {
            $ratingCollection->addEntitySummaryToItem($entityId, $this->_storeManager->getStore()->getId());
        }

        $this->assign('collection', $ratingCollection);
        return parent::_toHtml();
    }
}
