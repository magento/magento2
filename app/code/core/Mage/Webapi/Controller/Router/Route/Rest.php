<?php
/**
 * Route to resources available via REST API.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Controller_Router_Route_Rest extends Mage_Webapi_Controller_Router_Route
{
    /**#@+
     * Names of special parameters in routes.
     */
    const PARAM_VERSION = 'resourceVersion';
    const PARAM_ID = 'id';
    const PARAM_PARENT_ID = 'parentId';
    /**#@-*/

    /** @var string */
    protected $_resourceName;

    /** @var string */
    protected $_resourceType;

    /**
     * Set route resource.
     *
     * @param string $resourceName
     * @return Mage_Webapi_Controller_Router_Route_Rest
     */
    public function setResourceName($resourceName)
    {
        $this->_resourceName = $resourceName;
        return $this;
    }

    /**
     * Get route resource.
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resourceName;
    }

    /**
     * Set route resource type.
     *
     * @param string $resourceType
     * @return Mage_Webapi_Controller_Router_Route_Rest
     */
    public function setResourceType($resourceType)
    {
        $this->_resourceType = $resourceType;
        return $this;
    }

    /**
     * Get route resource type.
     *
     * @return string
     */
    public function getResourceType()
    {
        return $this->_resourceType;
    }
}
