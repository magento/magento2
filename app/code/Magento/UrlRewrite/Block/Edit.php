<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block;

/**
 * Block for URL rewrites edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\UrlRewrite\Block\Selector
     */
    private $_selectorBlock;

    /**
     * Part for building some blocks names
     *
     * @var string
     */
    protected $_controller = 'url_rewrite';

    /**
     * Generated buttons html cache
     *
     * @var string
     */
    protected $_buttonsHtml;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $_rewriteFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = []
    ) {
        $this->_rewriteFactory = $rewriteFactory;
        $this->_adminhtmlData = $adminhtmlData;
        parent::__construct($context, $data);
    }

    /**
     * Prepare URL rewrite editing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('edit.phtml');

        $this->_addBackButton();
        $this->_prepareLayoutFeatures();

        return parent::_prepareLayout();
    }

    /**
     * Prepare featured blocks for layout of URL rewrite editing
     *
     * @return void
     */
    protected function _prepareLayoutFeatures()
    {
        if ($this->_getUrlRewrite()->getId()) {
            $this->_headerText = __('Edit URL Rewrite');
        } else {
            $this->_headerText = __('Add New URL Rewrite');
        }

        $this->_addUrlRewriteSelectorBlock();
        $this->_addEditFormBlock();
    }

    /**
     * Add child edit form block
     *
     * @return void
     */
    protected function _addEditFormBlock()
    {
        $this->setChild('form', $this->_createEditFormBlock());

        if ($this->_getUrlRewrite()->getId()) {
            $this->_addResetButton();
            $this->_addDeleteButton();
        }

        $this->_addSaveButton();
    }

    /**
     * Add reset button
     *
     * @return void
     */
    protected function _addResetButton()
    {
        $this->addButton(
            'reset',
            [
                'label' => __('Reset'),
                'onclick' => '$(\'edit_form\').reset()',
                'class' => 'scalable',
                'level' => -1
            ]
        );
    }

    /**
     * Add back button
     *
     * @return void
     */
    protected function _addBackButton()
    {
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->_adminhtmlData->getUrl('adminhtml/*/') . '\')',
                'class' => 'back',
                'level' => -1
            ]
        );
    }

    /**
     * Update Back button location link
     *
     * @param string $link
     * @return void
     */
    protected function _updateBackButtonLink($link)
    {
        $this->updateButton('back', 'onclick', 'setLocation(\'' . $link . '\')');
    }

    /**
     * Add delete button
     *
     * @return void
     */
    protected function _addDeleteButton()
    {
        $this->addButton(
            'delete',
            [
                'label' => __('Delete'),
                'onclick' => 'deleteConfirm(' . json_encode(__('Are you sure you want to do this?'))
                    . ','
                    . json_encode(
                        $this->_adminhtmlData->getUrl(
                            'adminhtml/*/delete',
                            ['id' => $this->getUrlRewrite()->getId()]
                        )
                    )
                    . ')',
                'class' => 'scalable delete',
                'level' => -1
            ]
        );
    }

    /**
     * Add save button
     *
     * @return void
     */
    protected function _addSaveButton()
    {
        $this->addButton(
            'save',
            [
                'label' => __('Save'),
                'class' => 'save primary save-url-rewrite',
                'level' => -1,
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ]
            ]
        );
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\UrlRewrite\Block\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            \Magento\UrlRewrite\Block\Edit\Form::class,
            '',
            ['data' => ['url_rewrite' => $this->_getUrlRewrite()]]
        );
    }

    /**
     * Add child URL rewrite selector block
     *
     * @return void
     */
    protected function _addUrlRewriteSelectorBlock()
    {
        $this->setChild('selector', $this->_getSelectorBlock());
    }

    /**
     * Get selector block
     *
     * @return \Magento\UrlRewrite\Block\Selector
     */
    private function _getSelectorBlock()
    {
        if (!$this->_selectorBlock) {
            $this->_selectorBlock = $this->getLayout()->createBlock(\Magento\UrlRewrite\Block\Selector::class);
        }
        return $this->_selectorBlock;
    }

    /**
     * Get container buttons HTML
     *
     * Since buttons are set as children, we remove them as children after generating them
     * not to duplicate them in future
     *
     * @param null $area
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getButtonsHtml($area = null)
    {
        if (null === $this->_buttonsHtml) {
            $this->_buttonsHtml = parent::getButtonsHtml();
            $layout = $this->getLayout();
            foreach ($this->getChildNames() as $name) {
                $alias = $layout->getElementAlias($name);
                if (false !== strpos($alias, '_button')) {
                    $layout->unsetChild($this->getNameInLayout(), $alias);
                }
            }
        }
        return $this->_buttonsHtml;
    }

    /**
     * Get or create new instance of URL rewrite
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected function _getUrlRewrite()
    {
        if (!$this->hasData('url_rewrite')) {
            $this->setUrlRewrite($this->_rewriteFactory->create());
        }
        return $this->getUrlRewrite();
    }
}
