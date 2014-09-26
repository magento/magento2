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
namespace Magento\CatalogUrlRewrite\Service\V1;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Resource;

/**
 * Store view service
 */
class StoreViewService
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @param Config $eavConfig
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        Config $eavConfig,
        Resource $resource
    ) {
        $this->eavConfig = $eavConfig;
        $this->connection = $resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * Check that entity has overridden url key for specific store
     *
     * @param int $storeId
     * @param int $entityId
     * @param string $entityType
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function doesEntityHaveOverriddenUrlKeyForStore($storeId, $entityId, $entityType)
    {
        $attribute = $this->eavConfig->getAttribute($entityType, 'url_key');
        if (!$attribute) {
            throw new \InvalidArgumentException(sprintf('Cannot retrieve attribute for entity type "%s"', $entityType));
        }
        $select = $this->connection->select()
            ->from($attribute->getBackendTable(), 'store_id')
            ->where('attribute_id = ?', $attribute->getId())
            ->where('entity_id = ?', $entityId);

        return in_array($storeId, $this->connection->fetchCol($select));
    }
}
