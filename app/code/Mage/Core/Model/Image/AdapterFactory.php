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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Image_AdapterFactory
{
    const ADAPTER_GD2   = 'GD2';
    const ADAPTER_IM    = 'IMAGEMAGICK';

    const XML_PATH_IMAGE_ADAPTER = 'dev/image/adapter';

    /**
     * @var array
     */
    protected $_adapterClasses;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Store_Config
     */
    protected $_storeConfig;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Model_Config_Storage_WriterInterface
     */
    protected $_configWriter;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Helper_Data $helper
     * @param Mage_Core_Model_Config_Storage_WriterInterface $configWriter
     * @param Mage_Core_Model_Config $configModel
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Helper_Data $helper,
        Mage_Core_Model_Config_Storage_WriterInterface $configWriter,
        Mage_Core_Model_Config $configModel
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeConfig = $storeConfig;
        $this->_helper = $helper;
        $this->_configWriter = $configWriter;
        $this->_config = $configModel;
        $this->_adapterClasses = array(
            self::ADAPTER_GD2 => 'Varien_Image_Adapter_Gd2',
            self::ADAPTER_IM => 'Varien_Image_Adapter_ImageMagick',
        );
    }

    /**
     * Return specified image adapter
     *
     * @param string $adapterType
     * @return Varien_Image_Adapter_Abstract
     * @throws InvalidArgumentException
     * @throws Exception if some of dependecies are missing
     */
    public function create($adapterType = null)
    {
        if (!isset($adapterType)) {
            $adapterType = $this->_getImageAdapterType();
        }
        if (!isset($this->_adapterClasses[$adapterType])) {
            throw new InvalidArgumentException(
                $this->_helper->__('Invalid adapter selected.')
            );
        }
        $imageAdapter = $this->_objectManager->create($this->_adapterClasses[$adapterType]);
        $imageAdapter->checkDependencies();
        return $imageAdapter;
    }

    /**
     * Returns image adapter type
     *
     * @return string|null
     * @throws Mage_Core_Exception
     */
    public function _getImageAdapterType()
    {
        $adapterType = $this->_storeConfig->getConfig(self::XML_PATH_IMAGE_ADAPTER);
        if (!isset($adapterType)) {
            $errorMessage = '';
            foreach ($this->_adapterClasses as $adapter => $class) {
                try {
                    $this->_objectManager->create($class)->checkDependencies();

                    $this->_configWriter->save(
                        self::XML_PATH_IMAGE_ADAPTER,
                        $adapter,
                        Mage_Core_Model_Config::SCOPE_DEFAULT
                    );

                    $this->_config->reinit();
                    $adapterType = $adapter;
                    break;
                } catch (Exception $e) {
                    $errorMessage .= $e->getMessage();
                }
            }
            if (!isset($adapterType)) {
                 throw new Mage_Core_Exception($errorMessage);
            }
        }
        return $adapterType;
    }
}
