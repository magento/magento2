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
 * description
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar;

use Magento\Framework\View\Element\AbstractBlock;

class Add extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/attribute/set/toolbar/add.phtml';

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        if ($this->getToolbar()) {
            $this->getToolbar()->addChild(
                'save_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Save Attribute Set'),
                    'class' => 'save primary save-attribute-set',
                    'data_attribute' => array(
                        'mage-init' => array('button' => array('event' => 'save', 'target' => '#set-prop-form'))
                    )
                )
            );
            $this->getToolbar()->addChild(
                'back_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Back'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/') . '\')',
                    'class' => 'back'
                )
            );
        }

        $this->addChild('setForm', 'Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset');
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _getHeader()
    {
        return __('Add New Attribute Set');
    }

    /**
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('setForm');
    }

    /**
     * Return id of form, used by this block
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->getChildBlock('setForm')->getForm()->getId();
    }
}
