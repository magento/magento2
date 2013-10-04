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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Image;

class AdapterFactory
{
    const ADAPTER_GD2   = 'GD2';
    const ADAPTER_IM    = 'IMAGEMAGICK';

    const XML_PATH_IMAGE_ADAPTER = 'dev/image/adapter';

    /**
     * @var array
     */
    protected $_adapterClasses;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\Core\Model\Config\Storage\WriterInterface
     */
    protected $_configWriter;

    /**
     * @var \Magento\Core\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Core\Model\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Core\Model\Config $configModel
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Core\Model\Config\Storage\WriterInterface $configWriter,
        \Magento\Core\Model\Config $configModel
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeConfig = $storeConfig;
        $this->_configWriter = $configWriter;
        $this->_config = $configModel;
        $this->_adapterClasses = array(
            self::ADAPTER_GD2 => 'Magento\Image\Adapter\Gd2',
            self::ADAPTER_IM => 'Magento\Image\Adapter\ImageMagick',
        );
    }

    /**
     * Return specified image adapter
     *
     * @param string $adapterType
     * @return \Magento\Image\Adapter\AbstractAdapter
     * @throws \InvalidArgumentException
     * @throws \Exception if some of dependecies are missing
     */
    public function create($adapterType = null)
    {
        if (!isset($adapterType)) {
            $adapterType = $this->_getImageAdapterType();
        }
        if (!isset($this->_adapterClasses[$adapterType])) {
            throw new \InvalidArgumentException(
                __('Invalid adapter selected.')
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
     * @throws \Magento\Core\Exception
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
                        \Magento\Core\Model\Config::SCOPE_DEFAULT
                    );

                    $this->_config->reinit();
                    $adapterType = $adapter;
                    break;
                } catch (\Exception $e) {
                    $errorMessage .= $e->getMessage();
                }
            }
            if (!isset($adapterType)) {
                 throw new \Magento\Core\Exception($errorMessage);
            }
        }
        return $adapterType;
    }
}
