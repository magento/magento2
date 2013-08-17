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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend widget grid massaction additional action
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Backend_Block_Widget_Grid_Massaction_Additional extends Mage_Backend_Block_Widget_Form
{
    /**
     * @var Mage_Core_Model_Layout_Argument_HandlerFactory
     */
    protected $_handlerFactory;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Core_Model_Layout_Argument_HandlerFactory $handlerFactory
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_Layout_Argument_HandlerFactory $handlerFactory,
        array $data = array()
    ) {
        parent::__construct($context, $data);

        $this->_handlerFactory = $handlerFactory;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Backend_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        foreach ($this->getData('fields') as $itemId => $item) {
            $this->_prepareFormItem($item);
            $form->addField($itemId, $item['type'], $item);
        }
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare form item
     *
     * @param array $item
     */
    protected function _prepareFormItem(array &$item)
    {
        if ($item['type'] == 'select' && is_string($item['values'])) {
            $argumentHandler = $this->_handlerFactory->getArgumentHandlerByType('options');
            $item['values'] = $argumentHandler->process($item['values']);
        }
        $item['class'] = isset($item['class']) ? $item['class'] . ' absolute-advice' : 'absolute-advice';
    }
}