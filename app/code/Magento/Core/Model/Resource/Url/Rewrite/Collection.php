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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Url rewrite resource collection model class
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Resource\Url\Rewrite;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $resource);
        $this->_storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Url\Rewrite', 'Magento\Core\Model\Resource\Url\Rewrite');
    }

    /**
     * Filter collections by stores
     *
     * @param mixed $store
     * @param bool $withAdmin
     * @return \Magento\Core\Model\Resource\Url\Rewrite\Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!is_array($store)) {
            $store = array($this->_storeManager->getStore($store)->getId());
        }
        if ($withAdmin) {
            $store[] = 0;
        }

        $this->addFieldToFilter('store_id', array('in' => $store));

        return $this;
    }

    /**
     *  Add filter by catalog product Id
     *
     * @param int $productId
     * @return \Magento\Core\Model\Resource\Url\Rewrite\Collection
     */
    public function filterAllByProductId($productId)
    {
        $this->getSelect()
            ->where('id_path = ?', "product/{$productId}")
            ->orWhere('id_path LIKE ?', "product/{$productId}/%");

        return $this;
    }

    /**
     * Add filter by all catalog category
     *
     * @return \Magento\Core\Model\Resource\Url\Rewrite\Collection
     */
    public function filterAllByCategory()
    {
        $this->getSelect()
            ->where('id_path LIKE ?', "category/%");
        return $this;
    }
}
