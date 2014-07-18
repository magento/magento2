<?php
/**
 *
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
namespace Magento\Wishlist\Controller\Index;

use Magento\Core\App\Action\FormKeyValidator;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Wishlist\Controller\IndexInterface;
use Magento\Framework\App\Action;

class Allcart extends Action\Action implements IndexInterface
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier
     */
    protected $itemCarrier;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @param Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param FormKeyValidator $formKeyValidator
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        Context $context,
        WishlistProviderInterface $wishlistProvider,
        FormKeyValidator $formKeyValidator,
        ItemCarrier $itemCarrier
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->formKeyValidator = $formKeyValidator;
        $this->itemCarrier = $itemCarrier;
        parent::__construct($context);
    }

    /**
     * Add all items from wishlist to shopping cart
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->_forward('noroute');
            return;
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $this->_forward('noroute');
            return;
        }
        $redirectUrl = $this->itemCarrier->moveAllToCart($wishlist, $this->getRequest()->getParam('qty'));
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
