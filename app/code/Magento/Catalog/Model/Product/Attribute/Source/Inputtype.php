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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attribute source input types
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

class Inputtype extends \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_eventManager = $eventManager;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Get product input types as option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $inputTypes = array(
            array('value' => 'price', 'label' => __('Price')),
            array('value' => 'media_image', 'label' => __('Media Image'))
        );

        $response = new \Magento\Framework\Object();
        $response->setTypes(array());
        $this->_eventManager->dispatch('adminhtml_product_attribute_types', array('response' => $response));
        $_disabledTypes = array();
        $_hiddenFields = array();
        foreach ($response->getTypes() as $type) {
            $inputTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
        }

        if ($this->_coreRegistry->registry('attribute_type_hidden_fields') === null) {
            $this->_coreRegistry->register('attribute_type_hidden_fields', $_hiddenFields);
        }
        return array_merge(parent::toOptionArray(), $inputTypes);
    }
}
