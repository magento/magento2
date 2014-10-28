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
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset;

/**
 * Adminhtml block for fieldset of bundle product
 */
class Bundle extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
{
    /**
     * Returns string with json config for bundle product
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $options = array();
        $optionsArray = $this->getOptions();
        foreach ($optionsArray as $option) {
            $optionId = $option->getId();
            $options[$optionId] = array('id' => $optionId, 'selections' => array());
            foreach ($option->getSelections() as $selection) {
                $options[$optionId]['selections'][$selection->getSelectionId()] = array(
                    'can_change_qty' => $selection->getSelectionCanChangeQty(),
                    'default_qty' => $selection->getSelectionQty()
                );
            }
        }
        $config = array('options' => $options);
        return $this->jsonEncoder->encode($config);
    }
}
