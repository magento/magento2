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
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Model\Price;

/**
 * Bundle Product Price Index
 *
 * @method \Magento\Bundle\Model\Resource\Price\Index getResource()
 * @method \Magento\Bundle\Model\Price\Index setEntityId(int $value)
 * @method int getWebsiteId()
 * @method \Magento\Bundle\Model\Price\Index setWebsiteId(int $value)
 * @method int getCustomerGroupId()
 * @method \Magento\Bundle\Model\Price\Index setCustomerGroupId(int $value)
 * @method float getMinPrice()
 * @method \Magento\Bundle\Model\Price\Index setMinPrice(float $value)
 * @method float getMaxPrice()
 * @method \Magento\Bundle\Model\Price\Index setMaxPrice(float $value)
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Index extends \Magento\Model\AbstractModel
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Bundle\Model\Resource\Price\Index');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Bundle\Model\Resource\Price\Index
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Reindex Product price
     *
     * @param int $productId
     * @param int $priceType
     * @return $this
     */
    protected function _reindexProduct($productId, $priceType)
    {
        $this->_getResource()->reindexProduct($productId, $priceType);
        return $this;
    }

    /**
     * Reindex Bundle product Price Index
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Catalog\Model\Product\Condition\ConditionInterface|array|int $products
     * @return $this
     */
    public function reindex($products = null)
    {
        $this->_getResource()->reindex($products);
        return $this;
    }

    /**
     * Add bundle price range index to Product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return $this
     */
    public function addPriceIndexToCollection($collection)
    {
        $productObjects = array();
        $productIds = array();
        foreach ($collection->getItems() as $product) {
            /* @var $product \Magento\Catalog\Model\Product */
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $productIds[] = $product->getEntityId();
                $productObjects[$product->getEntityId()] = $product;
            }
        }
        $websiteId = $this->_storeManager->getStore($collection->getStoreId())->getWebsiteId();
        $groupId = $this->_customerSession->getCustomerGroupId();

        $addOptionsToResult = false;
        $prices = $this->_getResource()->loadPriceIndex($productIds, $websiteId, $groupId);
        foreach ($productIds as $productId) {
            if (isset($prices[$productId])) {
                $productObjects[$productId]->setData(
                    '_price_index',
                    true
                )->setData(
                    '_price_index_min_price',
                    $prices[$productId]['min_price']
                )->setData(
                    '_price_index_max_price',
                    $prices[$productId]['max_price']
                );
            } else {
                $addOptionsToResult = true;
            }
        }

        if ($addOptionsToResult) {
            $collection->addOptionsToResult();
        }

        return $this;
    }

    /**
     * Add price index to bundle product after load
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function addPriceIndexToProduct($product)
    {
        $websiteId = $product->getStore()->getWebsiteId();
        $groupId = $this->_customerSession->getCustomerGroupId();
        $prices = $this->_getResource()->loadPriceIndex($product->getId(), $websiteId, $groupId);
        if (isset($prices[$product->getId()])) {
            $product->setData(
                '_price_index',
                true
            )->setData(
                '_price_index_min_price',
                $prices[$product->getId()]['min_price']
            )->setData(
                '_price_index_max_price',
                $prices[$product->getId()]['max_price']
            );
        }
        return $this;
    }
}
