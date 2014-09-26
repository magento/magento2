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


/**
 * Wishlist block shared items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Share;

class Wishlist extends \Magento\Wishlist\Block\AbstractBlock
{
    /**
     * Customer instance
     *
     * @var \Magento\Customer\Service\V1\Data\Customer
     */
    protected $_customer = null;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService,
        array $data = array()
    ) {
        $this->_customerAccountService = $customerAccountService;
        parent::__construct(
            $context,
            $httpContext,
            $productFactory,
            $data
        );
    }

    /**
     * Prepare global layout
     *
     * @return $this
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->setTitle($this->getHeader());
        return $this;
    }

    /**
     * Retrieve Shared Wishlist Customer instance
     *
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    public function getWishlistCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = $this->_customerAccountService->getCustomer($this->_getWishlist()->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * Retrieve Page Header
     *
     * @return string
     */
    public function getHeader()
    {
        return __("%1's Wish List", $this->escapeHtml($this->getWishlistCustomer()->getFirstname()));
    }
}
