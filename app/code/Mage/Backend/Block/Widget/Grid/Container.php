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
 * Backend grid container block
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Backend_Block_Widget_Grid_Container extends Mage_Backend_Block_Widget_Container
{
    /**#@+
     * Initialization parameters in pseudo-constructor
     */
    const PARAM_BLOCK_GROUP = 'block_group';
    const PARAM_BUTTON_NEW  = 'button_new';
    const PARAM_BUTTON_BACK = 'button_back';
    /**#@-*/

    protected $_addButtonLabel;
    protected $_backButtonLabel;
    protected $_blockGroup = 'Mage_Backend';

    protected $_template = 'Mage_Backend::widget/grid/container.phtml';

    /**
     * Initialize object state with incoming parameters
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->hasData(self::PARAM_BLOCK_GROUP)) {
            $this->_blockGroup = $this->_getData(self::PARAM_BLOCK_GROUP);
        }
        if ($this->hasData(self::PARAM_BUTTON_NEW)) {
            $this->_addButtonLabel = $this->_getData(self::PARAM_BUTTON_NEW);
        } else {
            // legacy logic to support all descendants
            if (is_null($this->_addButtonLabel)) {
                $this->_addButtonLabel = $this->__('Add New');
            }
            $this->_addNewButton();
        }
        if ($this->hasData(self::PARAM_BUTTON_BACK)) {
            $this->_backButtonLabel = $this->_getData(self::PARAM_BUTTON_BACK);
        } else {
            // legacy logic
            if (is_null($this->_backButtonLabel)) {
                $this->_backButtonLabel = $this->__('Back');
            }
        }
    }

    protected function _prepareLayout()
    {
        // check if grid was created through the layout
        if (false === $this->getChildBlock('grid')) {
            $this->setChild(
                'grid',
                $this->getLayout()->createBlock(
                    $this->_blockGroup
                        . '_Block_'
                        . str_replace(' ', '_', ucwords(str_replace('_', ' ', $this->_controller)))
                        . '_Grid',
                    $this->_controller . '.grid')
                    ->setSaveParametersInSession(true)
            );
        }
        return parent::_prepareLayout();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    public function getAddButtonLabel()
    {
        return $this->_addButtonLabel;
    }

    public function getBackButtonLabel()
    {
        return $this->_backButtonLabel;
    }

    /**
     * Create "New" button
     */
    protected function _addNewButton()
    {
        $this->_addButton('add', array(
            'label'     => $this->getAddButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getCreateUrl() .'\')',
            'class'     => 'add',
        ));
    }

    protected function _addBackButton()
    {
        $this->_addButton('back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
    }

    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }
}
