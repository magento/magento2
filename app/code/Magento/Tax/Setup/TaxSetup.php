<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Setup;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetup;

/**
 * Tax Setup Resource Model
 */
class TaxSetup extends SalesSetup
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $_setupFactory;

    /**
     * Product type config
     *
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * Init
     *
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param ScopeConfigInterface $config
     * @param CategorySetupFactory $setupFactory
     * @param ConfigInterface $productTypeConfig
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        ScopeConfigInterface $config,
        CategorySetupFactory $setupFactory,
        ConfigInterface $productTypeConfig
    ) {
        $this->_setupFactory = $setupFactory;
        $this->productTypeConfig = $productTypeConfig;
    }

    /**
     * Gets catalog setup
     *
     * @param array $data
     * @return CategorySetup
     */
    public function getCatalogSetup(array $data = [])
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
