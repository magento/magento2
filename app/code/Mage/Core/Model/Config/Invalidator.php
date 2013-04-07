<?php
/**
 * Configuration objects invalidator. Invalidates all required configuration objects for total config reinitialisation
 *
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
class Mage_Core_Model_Config_Invalidator implements Mage_Core_Model_Config_InvalidatorInterface
{
    /**
     * Primary configuration
     *
     * @var Mage_Core_Model_ConfigInterface
     */
    protected $_primaryConfig;

    /**
     * Modules configuration
     *
     * @var Mage_Core_Model_ConfigInterface
     */
    protected $_modulesConfig;

    /**
     * Locales configuration
     *
     * @var Mage_Core_Model_ConfigInterface
     */
    protected $_localesConfig;

    /**
     * @param Mage_Core_Model_ConfigInterface $primaryConfig
     * @param Mage_Core_Model_ConfigInterface $modulesConfig
     * @param Mage_Core_Model_ConfigInterface $localesConfig
     */
    public function __construct(
        Mage_Core_Model_ConfigInterface $primaryConfig,
        Mage_Core_Model_ConfigInterface $modulesConfig,
        Mage_Core_Model_ConfigInterface $localesConfig
    ) {
        $this->_primaryConfig = $primaryConfig;
        $this->_modulesConfig = $modulesConfig;
        $this->_localesConfig = $localesConfig;
    }

    /**
     * Invalidate config objects
     */
    public function invalidate()
    {
        $this->_primaryConfig->reinit();
        $this->_modulesConfig->reinit();
        $this->_localesConfig->reinit();
    }
}
