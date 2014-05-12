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
namespace Magento\Tax\Model\Resource;

/**
 * Tax Setup Resource Model
 */
class Setup extends \Magento\Sales\Model\Resource\Setup
{
    /**
     * @var \Magento\Catalog\Model\Resource\SetupFactory
     */
    protected $_setupFactory;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @param \Magento\Eav\Model\Entity\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGroupCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Model\Resource\SetupFactory $setupFactory
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Setup\Context $context,
        $resourceName,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $attrGroupCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\Resource\SetupFactory $setupFactory,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        $moduleName = 'Magento_Tax',
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->_setupFactory = $setupFactory;
        $this->productTypeConfig = $productTypeConfig;
        parent::__construct(
            $context,
            $resourceName,
            $cache,
            $attrGroupCollectionFactory,
            $config,
            $moduleName,
            $connectionName
        );
    }

    /**
     * Load Tax Table Data
     *
     * @param string $table
     * @return array
     */
    protected function _loadTableData($table)
    {
        $table = $this->getTable($table);
        $select = $this->_connection->select();
        $select->from($table);
        return $this->_connection->fetchAll($select);
    }

    /**
     * @param array $data
     * @return \Magento\Catalog\Model\Resource\Setup
     */
    public function getCatalogResourceSetup(array $data = array())
    {
        return $this->_setupFactory->create($data);
    }

    /**
     * Get taxable product types
     *
     * @return array
     */
    public function getTaxableItems()
    {
        return $this->productTypeConfig->filter('taxable');
    }
}
