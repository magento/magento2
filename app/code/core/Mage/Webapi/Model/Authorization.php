<?php
/**
 * Web API authorization model.
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Authorization
{
    /**
     * Web API ACL config's resources root ID.
     */
    const API_ACL_RESOURCES_ROOT_ID = 'Mage_Webapi';

    /** @var Mage_Core_Model_Authorization */
    protected $_coreAuthorization;

    /** @var Mage_Webapi_Helper_Data */
    protected $_helper;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Helper_Data $helper
     * @param Mage_Core_Model_Authorization $coreAuthorization
     */
    public function __construct(
        Mage_Webapi_Helper_Data $helper,
        Mage_Core_Model_Authorization $coreAuthorization
    ) {
        $this->_helper = $helper;
        $this->_coreAuthorization = $coreAuthorization;
    }

    /**
     * Check permissions on specific resource in ACL.
     *
     * @param string $resource
     * @param string $method
     * @throws Mage_Webapi_Exception
     */
    public function checkResourceAcl($resource, $method)
    {
        $coreAuthorization = $this->_coreAuthorization;
        if (!$coreAuthorization->isAllowed($resource . Mage_Webapi_Model_Acl_Rule::RESOURCE_SEPARATOR . $method)
            && !$coreAuthorization->isAllowed(Mage_Webapi_Model_Authorization::API_ACL_RESOURCES_ROOT_ID)
        ) {
            throw new Mage_Webapi_Exception(
                $this->_helper->__('Access to resource is forbidden.'),
                Mage_Webapi_Exception::HTTP_FORBIDDEN
            );
        }
    }
}
