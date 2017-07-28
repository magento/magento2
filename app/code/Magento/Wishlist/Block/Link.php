<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * "My Wish List" link
 */
namespace Magento\Wishlist\Block;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Class Link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * Template name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Wishlist::link.phtml';

    /**
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $_wishlistHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        array $data = []
    ) {
        $this->_wishlistHelper = $wishlistHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if ($this->_wishlistHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getHref()
    {
        return $this->getUrl('wishlist');
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getLabel()
    {
        return __('My Wish List');
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
