<?php
/**
 * The list of available hooks
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Source;

class Hook implements \Magento\Core\Model\Option\ArrayInterface
{
    /**
     * Path to environments section in the config
     */
    const XML_PATH_WEBHOOK = 'global/webhook/webhooks';

    /**
     * Cache of options
     *
     * @var null|array
     */
    protected $_options = null;

    /** @var  \Magento\Core\Model\Config */
    private $_config;

    /**
     * @param \Magento\Core\Model\Config $config
     */
    public function __construct(\Magento\Core\Model\Config $config )
    {
        $this->_config = $config;
    }

    /**
     * Get available topics
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array();

            $configElement = $this->_config->getNode(self::XML_PATH_WEBHOOK);
            if ($configElement) {
                $this->_options = $configElement->asArray();
            }
        }

        return $this->_options;
    }

    /**
     * Scan config element to retrieve topics
     *
     * @return array
     */
    public function getTopicsForForm()
    {
        $elements = array();

        // process groups
        $elements = $this->_getTopicsForForm($this->toOptionArray(), array(), $elements);

        return $elements;
    }

    /**
     * Recursive helper function to dynamically build topic information for our form.
     * Seeks out nodes under 'webhook' stopping when it finds a leaf that contains 'label'
     * The value is constructed using the XML tree parents.
     * @param array $node
     * @param array $path
     * @param array $elements
     * @return array
     */
    protected function _getTopicsForForm($node, $path, $elements)
    {
        if (!empty($node['label'])) {
            $value = join('/', $path);

            $label = __($node['label']);

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
