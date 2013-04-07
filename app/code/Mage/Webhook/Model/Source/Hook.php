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
 * The list of available hooks
 */
class Mage_Webhook_Model_Source_Hook
{
    /**
     * Path to environments section in the config
     * @var string
     */
    const XML_PATH_WEBHOOK = 'global/webhook/webhooks';

    /**
     * Type for inform webhook
     */
    const INFORM_TYPE = 'inform';

    /**
     * Type for callback webhook
     */
    const CALLBACK_TYPE = 'callback';

    /**
     * The default type for a webhook if no type subelement exists.
     */
    const DEFAULT_TYPE = self::INFORM_TYPE;

    /**
     * Cash of options
     * @var null|array
     */
    protected $_options = null;

    /**
     * Get available topics
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options) {
            return $this->_options;
        }

        $this->_options = array();

        $config = Mage::getConfig()->getNode(self::XML_PATH_WEBHOOK);
        if (!$config) {
            return $this->_options;
        }
        $this->_options = $config->asArray();

        return $this->_options;
    }

    public function getTopicsForForm()
    {
        $elements = array();

        // process groups
        $elements = $this->_getTopicsForForm($this->toOptionArray(), array(), $elements);

        return $elements;
    }

    // TODO: Consider making elements a reference
    /**
     * Recursive helper function to dynamically build topic information for our form.
     * Seeks out nodes under 'webhook' stopping when it finds a leaf that contains 'label'
     * The value is constructed using the XML tree parents.
     * @param $node
     * @param $path
     * @param $elements
     * @return array
     */
    protected function _getTopicsForForm($node, $path, $elements)
    {
        if (!empty($node['label'])) {
            $value = join('/', $path);

            $type = self::DEFAULT_TYPE;
            if (!empty($node['type'])) {
                $type = $node['type'];
            }

            $label = Mage::helper('Mage_Webhook_Helper_Data')->__($node['label']);

            if ($type === self::CALLBACK_TYPE) {
                $label = Mage::helper('Mage_Webhook_Helper_Data')->__('%s (Callback)', $label);
            }

            $elements[] = array(
                'label' => $label,
                'value' => $value,
            );

            return $elements;
        }

        foreach ($node as $group => $child) {
            $path[] = $group;
            $elements = $this->_getTopicsForForm($child, $path, $elements);
            array_pop($path);
        }

        return $elements;
    }
}
