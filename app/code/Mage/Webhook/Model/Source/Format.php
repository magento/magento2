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

/**
 * The list of available formats
 */
class Mage_Webhook_Model_Source_Format
{
    /**
     * Path to environments section in the config
     * @var string
     */
    const XML_PATH_FORMATS = 'global/webhook/formats';

    /**
     * Cash of options
     * @var null|array
     */
    protected $_options = null;

    /**
     * Get available formats
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options) {
            return $this->_options;
        }

        $this->_options = array();

        $config = Mage::getConfig()->getNode(self::XML_PATH_FORMATS);
        if (!$config) {
            return $this->_options;
        }
        $this->_options = $config->asArray();

        return $this->_options;
    }

    public function getFormatsForForm()
    {
        $elements = array();
        // process groups
        foreach ($this->toOptionArray() as $formatName => $format) {
            if (!empty($format['label'])) {
                $elements[] = array(
                    'label' => Mage::helper('Mage_Webhook_Helper_Data')->__($format['label']),
                    'value' => $formatName,
                );
            }
        }

        return $elements;
    }
}
