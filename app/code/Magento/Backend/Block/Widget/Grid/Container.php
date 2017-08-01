<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * Backend grid container block
 *
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @api
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
class Container extends \Magento\Backend\Block\Widget\Container
{
    /**#@+
     * Initialization parameters in pseudo-constructor
     */
    const PARAM_BLOCK_GROUP = 'block_group';

    const PARAM_BUTTON_NEW = 'button_new';

    const PARAM_BUTTON_BACK = 'button_back';

    /**#@-*/

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_addButtonLabel;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_backButtonLabel;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_blockGroup = 'Magento_Backend';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/grid/container.phtml';

    /**
     * Initialize object state with incoming parameters
     *
     * @return void
     * @since 2.0.0
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
            if ($this->_addButtonLabel === null) {
                $this->_addButtonLabel = __('Add New');
            }
            $this->_addNewButton();
        }
        if ($this->hasData(self::PARAM_BUTTON_BACK)) {
            $this->_backButtonLabel = $this->_getData(self::PARAM_BUTTON_BACK);
        } else {
            // legacy logic
            if ($this->_backButtonLabel === null) {
                $this->_backButtonLabel = __('Back');
            }
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        // check if grid was created through the layout
        if (false === $this->getChildBlock('grid')) {
            $this->setChild(
                'grid',
                $this->getLayout()->createBlock(
                    str_replace(
                        '_',
                        '\\',
                        $this->_blockGroup
                    ) . '\\Block\\' . str_replace(
                        ' ',
                        '\\',
                        ucwords(str_replace('_', ' ', $this->_controller))
                    ) . '\\Grid',
                    $this->_controller . '.grid'
                )->setSaveParametersInSession(
                    true
                )
            );
        }
        return parent::_prepareLayout();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddButtonLabel()
    {
        return $this->_addButtonLabel;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getBackButtonLabel()
    {
        return $this->_backButtonLabel;
    }

    /**
     * Create "New" button
     *
     * @return void
     * @since 2.0.0
     */
    protected function _addNewButton()
    {
        $this->addButton(
            'add',
            [
                'label' => $this->getAddButtonLabel(),
                'onclick' => 'setLocation(\'' . $this->getCreateUrl() . '\')',
                'class' => 'add primary'
            ]
        );
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _addBackButton()
    {
        $this->addButton(
            'back',
            [
                'label' => $this->getBackButtonLabel(),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ]
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getHeaderWidth()
    {
        return 'width:50%;';
    }
}
