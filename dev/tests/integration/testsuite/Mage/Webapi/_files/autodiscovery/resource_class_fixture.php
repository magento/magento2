<?php
/**
 * Tests fixture for Auto Discovery functionality.
 *
 * Fake resource controller.
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
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Vendor_Module_Controller_Webapi_Resource
{
    /**
     * @param Vendor_Module_Model_Webapi_CustomerData[] $resourceData
     * @param bool $requiredField
     * @param string $optionalField
     * @param int $secondOptional
     * @return Vendor_Module_Model_Webapi_CustomerData
     */
    public function createV1($resourceData, $requiredField, $optionalField = 'optionalField', $secondOptional = 1)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $resourceId
     * @param Vendor_Module_Model_Webapi_CustomerData $resourceData
     * @param float $additionalRequired
     */
    public function updateV2($resourceId, $resourceData, $additionalRequired)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $resourceId
     * @return Vendor_Module_Model_Webapi_CustomerData
     */
    public function getV2($resourceId)
    {
        // Body is intentionally omitted
    }

    /**
     * @apiDeprecated vendorModuleResource::listV3
     * @param float $additionalRequired
     * @param bool $optional
     * @return Vendor_Module_Model_Webapi_CustomerData[]
     */
    public function listV2($additionalRequired, $optional = false)
    {
        // Body is intentionally omitted
    }

    public function listV3()
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $deleteId The name of this parameter MUST be different from name used in get method.
     * @apiRemoved deleteV3
     */
    public function deleteV1($deleteId)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $deleteId The name of this parameter MUST be different from name used in get method.
     * @apiDeprecated Vendor_Module_Controller_Webapi_Resource_Subresource::deleteV3
     */
    public function deleteV2($deleteId)
    {
        // Body is intentionally omitted
    }

    /**
     * @apiDeprecated
     * @apiRemoved
     * @param int $deleteId The name of this parameter MUST be different from name used in get method.
     */
    public function deleteV3($deleteId)
    {
        // Body is intentionally omitted
    }

    /**
     * @apiDeprecated
     * @param int $deleteId The name of this parameter MUST be different from name used in get method.
     */
    public function deleteV4($deleteId)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int $deleteId The name of this parameter MUST be different from name used in get method.
     */
    public function deleteV5($deleteId)
    {
        // Body is intentionally omitted
    }

    /**
     * @param Vendor_Module_Model_Webapi_CustomerData[] $resourceData
     */
    public function multiUpdateV2($resourceData)
    {
        // Body is intentionally omitted
    }

    /**
     * @param int[] $idsToBeRemoved
     */
    public function multiDeleteV2($idsToBeRemoved)
    {
        // Body is intentionally omitted
    }

    /**
     * Test situation of internal method, that should not be exposed through API.
     */
    public function someMethodThatWillBeSkipped()
    {
        // Body is intentionally omitted
    }
}
