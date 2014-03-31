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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

class Collection extends \Magento\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Store Manager Model
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
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
     * @return $this
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
     * @return $this
     */
    public function filterAllByProductId($productId)
    {
        $this->getSelect()->where(
            'id_path = ?',
            "product/{$productId}"
        )->orWhere(
            'id_path LIKE ?',
            "product/{$productId}/%"
        );

        return $this;
    }

    /**
     * Add filter by all catalog category
     *
     * @return $this
     */
    public function filterAllByCategory()
    {
        $this->getSelect()->where('id_path LIKE ?', "category/%");
        return $this;
    }
}
