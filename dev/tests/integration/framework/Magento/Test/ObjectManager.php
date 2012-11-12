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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_ObjectManager extends Magento_ObjectManager_Zend
{
    /**
     * @param string $definitionsFile
     * @param Zend\Di\Di $diInstance
     */
    public function __construct($definitionsFile = null, Zend\Di\Di $diInstance = null)
    {
        $diInstance = $diInstance ? $diInstance : new Magento_Di();
        $diInstance->setInstanceManager(new Magento_Test_Di_InstanceManager());
        parent::__construct($definitionsFile, $diInstance);
    }

    /**
     * Clear InstanceManager cache
     *
     * @return Magento_Test_ObjectManager
     */
    public function clearCache()
    {
        $resource = $this->get('Mage_Core_Model_Resource');
        $this->_di->setInstanceManager(new Magento_Test_Di_InstanceManager());
        $this->addSharedInstance($this, 'Magento_ObjectManager');
        $this->addSharedInstance($resource, 'Mage_Core_Model_Resource');

        return $this;
    }

    /**
     * Add shared instance
     *
     * @param object $instance
     * @param string $classOrAlias
     * @return Magento_Test_ObjectManager
     * @throws Zend\Di\Exception\InvalidArgumentException
     */
    public function addSharedInstance($instance, $classOrAlias)
    {
        $this->_di->instanceManager()->addSharedInstance($instance, $classOrAlias);

        return $this;
    }

    /**
     * Remove shared instance
     *
     * @param string $classOrAlias
     * @return Magento_Test_ObjectManager
     */
    public function removeSharedInstance($classOrAlias)
    {
        /** @var $instanceManager Magento_Test_Di_InstanceManager */
        $instanceManager = $this->_di->instanceManager();
        $instanceManager->removeSharedInstance($classOrAlias);

        return $this;
    }
}
