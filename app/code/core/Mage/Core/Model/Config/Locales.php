<?php
/**
 *  Locale configuration. Contains configuration from app/locale/[locale_Code]/*.xml files
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
class Mage_Core_Model_Config_Locales implements Mage_Core_Model_ConfigInterface
{
    /**
     * Configuration data container
     *
     * @var Mage_Core_Model_ConfigInterface
     */
    protected $_data;

    /**
     * Configuration storage
     *
     * @var Mage_Core_Model_Config_StorageInterface
     */
    protected $_storage;

    /**
     * @param Mage_Core_Model_Config_StorageInterface $storage
     */
    public function __construct(Mage_Core_Model_Config_StorageInterface $storage)
    {
        $this->_storage = $storage;
        $this->_data = $this->_storage->getConfiguration();
    }

    /**
     * Get configuration node
     *
     * @param string $path
     * @return Varien_Simplexml_Element
     */
    public function getNode($path = null)
    {
        return $this->_data->getNode($path);
    }

    /**
     * Create node by $path and set its value
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param boolean $overwrite
     */
    public function setNode($path, $value, $overwrite = true)
    {
        $this->_data->setNode($path, $value, $overwrite);
    }

    /**
     * Returns nodes found by xpath expression
     *
     * @param string $xpath
     * @return array
     */
    public function getXpath($xpath)
    {
        return $this->_data->getXpath($xpath);
    }

    /**
     * Reinitialize locales configuration
     */
    public function reinit()
    {
        $this->_data = $this->_storage->getConfiguration();
    }
}
