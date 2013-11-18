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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Mass Action processing model
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product;

class Action extends \Magento\Core\Model\AbstractModel
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Index indexer
     *
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexIndexer;

    /**
     * Product website factory
     *
     * @var \Magento\Catalog\Model\Product\WebsiteFactory
     */
    protected $_productWebsiteFactory;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\WebsiteFactory $productWebsiteFactory
     * @param \Magento\Index\Model\Indexer $indexIndexer
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Product\WebsiteFactory $productWebsiteFactory,
        \Magento\Index\Model\Indexer $indexIndexer,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_productWebsiteFactory = $productWebsiteFactory;
        $this->_indexIndexer = $indexIndexer;
        $this->_eventManager = $eventManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Product\Action');
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return \Magento\Catalog\Model\Resource\Product\Action
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return \Magento\Catalog\Model\Product\Action
     */
    public function updateAttributes($productIds, $attrData, $storeId)
    {
        $this->_eventManager->dispatch('catalog_product_attribute_update_before', array(
            'attributes_data' => &$attrData,
            'product_ids'   => &$productIds,
            'store_id'      => &$storeId
        ));

        $this->_getResource()->updateAttributes($productIds, $attrData, $storeId);
        $this->setData(array(
            'product_ids'       => array_unique($productIds),
            'attributes_data'   => $attrData,
            'store_id'          => $storeId
        ));

        // register mass action indexer event
        $this->_indexIndexer->processEntityAction(
            $this, \Magento\Catalog\Model\Product::ENTITY, \Magento\Index\Model\Event::TYPE_MASS_ACTION
        );
        return $this;
    }

    /**
     * Update websites for product action
     *
     * allowed types:
     * - add
     * - remove
     *
     * @param array $productIds
     * @param array $websiteIds
     * @param string $type
     */
    public function updateWebsites($productIds, $websiteIds, $type)
    {
        if ($type == 'add') {
            $this->_productWebsiteFactory->create()->addProducts($websiteIds, $productIds);
        } else if ($type == 'remove') {
            $this->_productWebsiteFactory->create()->removeProducts($websiteIds, $productIds);
        }

        $this->setData(array(
            'product_ids' => array_unique($productIds),
            'website_ids' => $websiteIds,
            'action_type' => $type
        ));

        // register mass action indexer event
        $this->_indexIndexer->processEntityAction(
            $this, \Magento\Catalog\Model\Product::ENTITY, \Magento\Index\Model\Event::TYPE_MASS_ACTION
        );
    }
}
