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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Formatter_Factory
{
    const XML_PATH_FORMATS = 'global/webhook/formats/';

    protected $_config;

    public function __construct(Mage_Core_Model_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Get formatter factory for specified format
     *
     * @param string $format
     * @return Mage_Webhook_Model_Formatter_Factory_Interface
     * @throws LogicException
     */
    public function getFormatterFactory($format)
    {
        $modelName = (string) $this->_config->getNode(self::XML_PATH_FORMATS . $format . '/formatter_factory');

        if (!$modelName) {
            throw new LogicException("Wrong Format name $format.");
        }

        $factory = $this->getModel($modelName);
        if (!$factory instanceof Mage_Webhook_Model_Formatter_Factory_Interface) {
            throw new LogicException("Wrong Formatter type for format $format.");
        }

        return $factory;
    }

    protected function getModel($modelName)
    {
        // TODO: probably here we should pass only format configuration options
        return Mage::getModel($modelName, $this->_config);
    }
}