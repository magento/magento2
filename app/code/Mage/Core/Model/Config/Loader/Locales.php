<?php
/**
 * Locale configuration loader
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
class Mage_Core_Model_Config_Loader_Locales implements Mage_Core_Model_Config_LoaderInterface
{
    /**
     * Base dirs model
     *
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * Element prototype factory
     *
     * @var Mage_Core_Model_Config_BaseFactory
     */
    protected $_factory;

    /**
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Config_BaseFactory $factory
     */
    public function __construct(Mage_Core_Model_Dir $dirs, Mage_Core_Model_Config_BaseFactory $factory)
    {
        $this->_dirs = $dirs;
        $this->_factory = $factory;
    }

    /**
     * Populate configuration object
     * Load locale configuration from locale configuration files
     *
     * @param Mage_Core_Model_Config_Base $config
     */
    public function load(Mage_Core_Model_Config_Base $config)
    {
        $localeDir = $this->_dirs->getDir(Mage_Core_Model_Dir::LOCALE);
        $files = glob($localeDir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'config.xml');

        if (is_array($files) && !empty($files)) {
            foreach ($files as $file) {
                $merge = $this->_factory->create();
                $merge->loadFile($file);
                $config->extend($merge);
            }
        }
    }
}
