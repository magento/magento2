<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Data\Design;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Theme\Api\Data\DesignConfigInterface;

class Config extends AbstractExtensibleObject implements DesignConfigInterface
{
    /**
     * Design config grid indexer id
     */
    const DESIGN_CONFIG_GRID_INDEXER_ID = 'design_config_grid';

    /**
     * @inheritDoc
     */
    public function getScope()
    {
        return $this->_get(self::SCOPE);
    }

    /**
     * @inheritDoc
     */
    public function getScopeId()
    {
        return $this->_get(self::SCOPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setScope($scope)
    {
        return $this->setData(self::SCOPE, $scope);
    }

    /**
     * @inheritDoc
     */
    public function setScopeId($scopeId = null)
    {
        return $this->setData(self::SCOPE_ID, $scopeId);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(\Magento\Theme\Api\Data\DesignConfigExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
