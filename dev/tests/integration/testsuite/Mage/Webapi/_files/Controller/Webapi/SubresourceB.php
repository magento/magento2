<?php
/**
 * API Resource action controller fixture.
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
class NamespaceA_ModuleA_Controller_Webapi_ModuleA_SubresourceB extends Mage_Webapi_Controller_ActionAbstract
{
    /**
     * Subresource description.
     *
     * @param int $subresourceId ID of subresource.
     * @return NamespaceA_ModuleA_Model_Webapi_ModuleAData Data of resource
     */
    public function getV1($subresourceId)
    {

    }

    /**
     * List description.
     *
     * @param int $parentId Id of parent resource
     * @return NamespaceA_ModuleA_Model_Webapi_ModuleAData[] list of resources
     */
    public function listV1($parentId)
    {

    }
}
