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
 * @package     Magento_Di
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Zend\Di\InstanceManager;

class Magento_Di_InstanceManager_Zend extends InstanceManager implements Magento_Di_InstanceManager
{
    /**
     * @var Magento_Di_Generator
     */
    protected $_generator;

    /**
     * @param Magento_Di_Generator $classGenerator
     */
    public function __construct(Magento_Di_Generator $classGenerator = null)
    {
        $this->_generator = $classGenerator ?: new Magento_Di_Generator();
    }

    /**
     * Remove shared instance
     *
     * @param string $classOrAlias
     * @return Magento_Di_InstanceManager_Zend
     */
    public function removeSharedInstance($classOrAlias)
    {
        unset($this->sharedInstances[$classOrAlias]);

        return $this;
    }

    /**
     * Add type preference from configuration
     *
     * @param string $interfaceOrAbstract
     * @param string $implementation
     * @return Zend\Di\InstanceManager
     */
    public function addTypePreference($interfaceOrAbstract, $implementation)
    {
        $this->_generator->generateClass($implementation);
        return parent::addTypePreference($interfaceOrAbstract, $implementation);
    }

    /**
     * Set parameters from configuration
     *
     * @param string $aliasOrClass
     * @param array $parameters
     */
    public function setParameters($aliasOrClass, array $parameters)
    {
        foreach ($parameters as $parameter) {
            if (is_string($parameter)) {
                $this->_generator->generateClass($parameter);
            }
        }
        parent::setParameters($aliasOrClass, $parameters);
    }
}
