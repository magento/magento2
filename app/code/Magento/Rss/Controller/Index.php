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
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Rss\Controller;

use Magento\App\Action\NotFoundException;

class Index extends \Magento\App\Action\Action
{
    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Rss\Helper\WishlistRss
     */
    protected $_wishlistHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Rss\Helper\WishlistRss $wishlistHelper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Rss\Helper\WishlistRss $wishlistHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_storeConfig = $storeConfig;
        $this->_wishlistHelper = $wishlistHelper;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return void
     * @throws NotFoundException
     */
    public function indexAction()
    {
        if ($this->_storeConfig->getConfig('rss/config/active')) {
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            throw new NotFoundException();
        }
    }

    /**
     * Display feed not found message
     *
     * @return void
     */
    public function nofeedAction()
    {
        $this->getResponse()->setHeader(
            'HTTP/1.1',
            '404 Not Found'
        )->setHeader(
            'Status',
            '404 File not found'
        )->setHeader(
            'Content-Type',
            'text/plain; charset=UTF-8'
        )->setBody(
            __('There was no RSS feed enabled.')
        );
    }

    /**
     * Wishlist rss feed action
     * Show all public wishlists and private wishlists that belong to current user
     *
     * @return void
     */
    public function wishlistAction()
    {
        if ($this->_storeConfig->getConfig('rss/wishlist/active')) {
            $wishlist = $this->_wishlistHelper->getWishlist();
            if ($wishlist && ($wishlist->getVisibility()
                || $this->_customerSession->authenticate($this)
                    && $wishlist->getCustomerId() == $this->_wishlistHelper->getCustomer()->getId())
            ) {
                $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8');
                $this->_view->loadLayout(false);
                $this->_view->renderLayout();
                return;
            }
        }
        $this->nofeedAction();
    }
}
