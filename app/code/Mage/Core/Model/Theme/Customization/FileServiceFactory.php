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

/**
 * Theme customization files factory
 */
class Mage_Core_Model_Theme_Customization_FileServiceFactory
{
    /**
     * XML path to definitions of customization services
     */
    const XML_PATH_CUSTOM_FILES = 'default/theme/customization';

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @var array
     */
    protected $_types = array();

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Config $config
     */
    public function __construct(Magento_ObjectManager $objectManager, Mage_Core_Model_Config $config)
    {
        $this->_objectManager = $objectManager;
        $this->_config = $config;

        $convertNode = $config->getNode(self::XML_PATH_CUSTOM_FILES);
        if ($convertNode) {
            /** @var $node Mage_Core_Model_Config_Element */
            foreach ($convertNode->children() as $node) {
                $this->_types[$node->getName()] = strval($node);
            }
        }
    }

    /**
     * Create new instance
     *
     * @param $type
     * @param array $data
     * @return Mage_Core_Model_Theme_Customization_FileInterface
     * @throws InvalidArgumentException
     */
    public function create($type, array $data = array())
    {
        if (empty($this->_types[$type])) {
            throw new InvalidArgumentException('Unsupported file type');
        }
        $fileService = $this->_objectManager->get($this->_types[$type], array($data));
        if (!$fileService instanceof Mage_Core_Model_Theme_Customization_FileInterface) {
            throw new InvalidArgumentException('Service don\'t implement interface');
        }
        return $fileService;
    }
}
