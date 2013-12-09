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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Service;

/**
 * Integration Service Interface
 */
interface IntegrationV1Interface
{

    /**
     * Create a new Integration
     *
     * @param array $integrationData
     * @return array Integration data
     * @throws \Magento\Integration\Exception
     */
    public function create(array $integrationData);

    /**
     * Get the details of a specific Integration.
     *
     * @param int $integrationId
     * @return array Integration data
     * @throws \Magento\Integration\Exception
     */
    public function get($integrationId);

    /**
     * Find Integration by name.
     *
     * @param int $integrationName
     * @return array|null Integration data or null if not found
     * @throws \Magento\Integration\Exception
     */
    public function findByName($integrationName);


    /**
     * Update a Integration.
     *
     * @param array $integrationData
     * @return array Integration data
     * @throws \Magento\Integration\Exception
     */
    public function update(array $integrationData);

}
