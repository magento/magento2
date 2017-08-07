<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Data\Design;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Theme\Api\Data\DesignConfigInterface;

/**
 * Class \Magento\Theme\Model\Data\Design\Config
 *
 * @since 2.1.0
 */
class Config extends AbstractExtensibleObject implements DesignConfigInterface
{
    /**
     * Design config grid indexer id
     */
    const DESIGN_CONFIG_GRID_INDEXER_ID = 'design_config_grid';

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getScope()
    {
        return $this->_get(self::SCOPE);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getScopeId()
    {
        return $this->_get(self::SCOPE_ID);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setScope($scope)
    {
        return $this->setData(self::SCOPE, $scope);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setScopeId($scopeId = null)
    {
        return $this->setData(self::SCOPE_ID, $scopeId);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setExtensionAttributes(\Magento\Theme\Api\Data\DesignConfigExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
