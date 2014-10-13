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
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\StoreManagerInterface;
use Magento\Store\Model\Store;

class DataProvider implements DataProviderInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $eavConfig
     * @param Resource $resource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Config $eavConfig, Resource $resource, StoreManagerInterface $storeManager)
    {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSet(BucketInterface $bucket, RequestInterface $request)
    {
        $currentStore = $request->getScopeDimension()->getValue();
        $currentStoreId = $this->storeManager->getStore($currentStore)->getId();
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());
        $table = $attribute->getBackendTable();

        $ifNullCondition = $this->getConnection()->getIfNullSql('current_store.value', 'main_table.value');

        $select = $this->getSelect();
        $select->from(['main_table' => $table], null)
            ->joinLeft(
                ['current_store' => $table],
                'current_store.attribute_id = main_table.attribute_id AND current_store.store_id = ' . $currentStoreId,
                null
            )
            ->columns([BucketInterface::FIELD_VALUE => $ifNullCondition])
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ?', Store::DEFAULT_STORE_ID);

        return $select;
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->getConnection()->select();
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }
}
