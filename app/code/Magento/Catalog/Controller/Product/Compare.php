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
namespace Magento\Catalog\Controller\Product;

use Magento\Core\App\Action\FormKeyValidator;

/**
 * Catalog compare controller
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Compare extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Catalog product compare list
     *
     * @var \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    protected $_catalogProductCompareList;

    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Item collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Compare item factory
     *
     * @var \Magento\Catalog\Model\Product\Compare\ItemFactory
     */
    protected $_compareItemFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\StoreManagerInterface $storeManager,
        FormKeyValidator $formKeyValidator
    ) {
        $this->_storeManager = $storeManager;
        $this->_compareItemFactory = $compareItemFactory;
        $this->_productFactory = $productFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        $this->_catalogProductCompareList = $catalogProductCompareList;
        $this->_catalogSession = $catalogSession;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Setter for customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->_customerId = $customerId;
        return $this;
    }
}
