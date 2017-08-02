<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Block\Adminhtml;

/**
 * Adminhtml wishlist report page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Wishlist extends \Magento\Backend\Block\Template
{
    /**
     * Template file
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'report/wishlist.phtml';

    /**
     * Reports wishlist collection factory
     *
     * @var \Magento\Reports\Model\ResourceModel\Wishlist\CollectionFactory
     * @since 2.0.0
     */
    protected $_wishlistFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Wishlist\CollectionFactory $wishlistFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Reports\Model\ResourceModel\Wishlist\CollectionFactory $wishlistFactory,
        array $data = []
    ) {
        $this->_wishlistFactory = $wishlistFactory;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Wishlist\Grid::class, 'report.grid')
        );

        $collection = $this->_wishlistFactory->create();

        list($customerWithWishlist, $wishlistsCount) = $collection->getWishlistCustomerCount();
        $this->setCustomerWithWishlist($customerWithWishlist);
        $this->setWishlistsCount($wishlistsCount);
        $this->setItemsBought(0);
        $this->setSharedCount($collection->getSharedCount());
        $this->setReferralsCount(0);
        $this->setConversionsCount(0);

        return $this;
    }
}
