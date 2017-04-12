<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface DesignConfigInterface
 * @api
 */
interface DesignConfigInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SCOPE = 'scope';
    const SCOPE_ID = 'scope_id';
    /**#@-*/

    /**
     * Return setting scope
     *
     * @return string
     */
    public function getScope();

    /**
     * Return scope identifier
     *
     * @return string
     */
    public function getScopeId();

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope);

    /**
     * @param string $scopeId
     * @return $this
     */
    public function setScopeId($scopeId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Theme\Api\Data\DesignConfigExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Theme\Api\Data\DesignConfigExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Theme\Api\Data\DesignConfigExtensionInterface $extensionAttributes);
}
