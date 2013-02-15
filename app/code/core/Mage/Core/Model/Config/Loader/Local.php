<?php
/**
 * Local Application configuration loader (app/etc/local.xml)
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
class Mage_Core_Model_Config_Loader_Local implements Mage_Core_Model_Config_LoaderInterface
{
    /**
     * Local configuration file
     */
    const LOCAL_CONFIG_FILE = 'local.xml';

    /**
     * Directory registry
     *
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * Configuration prototype Factory
     *
     * @var Mage_Core_Model_Config_BaseFactory
     */
    protected $_prototypeFactory;

    /**
     * Custom config file
     *
     * @var string
     */
    protected $_customFile;

    /**
     * Custom configuration string
     *
     * @var string
     */
    protected $_customConfig;

    /**
     * @param Mage_Core_Model_Config_BaseFactory $prototypeFactory
     * @param Mage_Core_Model_Dir $dirs
     * @param string $customConfig
     * @param string $customFile
     */
    public function __construct(
        Mage_Core_Model_Config_BaseFactory $prototypeFactory,
        Mage_Core_Model_Dir $dirs,
        $customConfig = null,
        $customFile = null
    ) {
        $this->_dirs = $dirs;
        $this->_customFile = $customFile;
        $this->_customConfig = $customConfig;
        $this->_prototypeFactory = $prototypeFactory;
    }

    /**
     * Populate configuration object
     *
     * @param Mage_Core_Model_Config_Base $config
     */
    public function load(Mage_Core_Model_Config_Base $config)
    {
        $etcDir = $this->_dirs->getDir(Mage_Core_Model_Dir::CONFIG);
        $localConfigParts = array();

        $localConfigFile = $etcDir . DIRECTORY_SEPARATOR . self::LOCAL_CONFIG_FILE;
        if (file_exists($localConfigFile)) {
            // 1. app/etc/local.xml
            $localConfig = $this->_prototypeFactory->create('<config/>');
            $localConfig->loadFile($localConfigFile);
            $localConfigParts[] = $localConfig;

            // 2. app/etc/<dir>/<file>.xml
            if (preg_match('/^[a-z\d_-]+(\/|\\\)+[a-z\d_-]+\.xml$/', $this->_customFile)) {
                $localConfigExtraFile = $etcDir . DIRECTORY_SEPARATOR . $this->_customFile;
                $localConfig = $this->_prototypeFactory->create('<config/>');
                $localConfig->loadFile($localConfigExtraFile);
                $localConfigParts[] = $localConfig;
            }
        }

        // 3. extra local configuration string
        if ($this->_customConfig) {
            $localConfig = $this->_prototypeFactory->create('<config/>');
            $localConfig->loadString($this->_customConfig);
            $localConfigParts[] = $localConfig;
        }

        if ($localConfigParts) {
            foreach ($localConfigParts as $oneConfigPart) {
                $config->extend($oneConfigPart);
            }
        }
    }
}
