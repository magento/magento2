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
class Mage_Webhook_Model_Formatter_Factory_Json implements Mage_Webhook_Model_Formatter_Factory_Interface
{
    const XML_PATH_DEFAULT_OPTIONS = 'global/webhook/formats/json/options/';

    protected $_config;

    public function __construct(Mage_Core_Model_Config $config)
    {
        $this->_config = $config;
    }

    /**
     * @param $format string indicating the format used
     * @return Mage_Webhook_Model_Formatter_Interface
     */
    public function getFormatter($format)
    {
        $modelName = (string) $this->_config->getNode(
            self::XML_PATH_DEFAULT_OPTIONS . 'format/' . $format . '/formatter'
        );

        if (!$modelName) {
            $modelName = (string) $this->_config->getNode(self::XML_PATH_DEFAULT_OPTIONS . 'default_formatter');
        }

        if (!$modelName) {
            throw new LogicException(
                "There is no specific formatter for the format given $format and no default formatter."
            );
        }

        $formatter = $this->getModel($modelName);
        if (!$formatter instanceof Mage_Webhook_Model_Formatter_Interface) {
            throw new LogicException("Wrong Formatter type for the model found given the format $format.");
        }

        return $formatter;
    }

    protected function getModel($modelName)
    {
        // TODO: probably here we should pass only format configuration options
        return Mage::getModel($modelName, $this->_config);
    }
}