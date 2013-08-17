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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Magento_ObjectManager_Factory
{
    /**
     * Set object manager
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function setObjectManager(Magento_ObjectManager $objectManager);

    /**
     * Set object manager configuration
     *
     * @param Magento_ObjectManager_Config $config
     */
    public function setConfig(Magento_ObjectManager_Config $config);

    /**
     * Retrieve definitions
     *
     * @return Magento_ObjectManager_Definition
     */
    public function getDefinitions();

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws LogicException
     * @throws BadMethodCallException
     */
    public function create($requestedType, array $arguments = array());
}
