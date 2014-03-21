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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Urlrewrite;

/**
 * Block for URL rewrites edit page
 *
 * @method \Magento\Core\Model\Url\Rewrite getUrlRewrite()
 * @method \Magento\Backend\Block\Urlrewrite\Edit setUrlRewrite(\Magento\Core\Model\Url\Rewrite $urlRewrite)
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Edit extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Backend\Block\Urlrewrite\Selector
     */
    private $_selectorBlock;

    /**
     * Part for building some blocks names
     *
     * @var string
     */
    protected $_controller = 'urlrewrite';

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
     * @var \Magento\Core\Model\Url\RewriteFactory
     */
    protected $_rewriteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Url\RewriteFactory $rewriteFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Url\RewriteFactory $rewriteFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = array()
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
        $this->setTemplate('urlrewrite/edit.phtml');

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

        $this->_updateBackButtonLink(
            $this->_adminhtmlData->getUrl('adminhtml/*/edit') . $this->_getSelectorBlock()->getDefaultMode()
        );
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
        $this->_addButton(
            'reset',
            array(
                'label' => __('Reset'),
                'onclick' => '$(\'edit_form\').reset()',
                'class' => 'scalable',
                'level' => -1
            )
        );
    }

    /**
     * Add back button
     *
     * @return void
     */
    protected function _addBackButton()
    {
        $this->_addButton(
            'back',
            array(
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->_adminhtmlData->getUrl('adminhtml/*/') . '\')',
                'class' => 'back',
                'level' => -1
            )
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
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $link . '\')');
    }

    /**
     * Add delete button
     *
     * @return void
     */
    protected function _addDeleteButton()
    {
        $this->_addButton(
            'delete',
            array(
                'label' => __('Delete'),
                'onclick' => 'deleteConfirm(\'' . addslashes(
                    __('Are you sure you want to do this?')
                ) . '\', \'' . $this->_adminhtmlData->getUrl(
                    'adminhtml/*/delete',
                    array('id' => $this->getUrlRewrite()->getId())
                ) . '\')',
                'class' => 'scalable delete',
                'level' => -1
            )
        );
    }

    /**
     * Add save button
     *
     * @return void
     */
    protected function _addSaveButton()
    {
        $this->_addButton(
            'save',
            array(
                'label' => __('Save'),
                'class' => 'save',
                'level' => -1,
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'save', 'target' => '#edit_form'))
                )
            )
        );
    }

    /**
     * Creates edit form block
     *
     * @return \Magento\Backend\Block\Urlrewrite\Edit\Form
     */
    protected function _createEditFormBlock()
    {
        return $this->getLayout()->createBlock(
            'Magento\Backend\Block\Urlrewrite\Edit\Form',
            '',
            array('data' => array('url_rewrite' => $this->_getUrlRewrite()))
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
     * @return \Magento\Backend\Block\Urlrewrite\Selector
     */
    private function _getSelectorBlock()
    {
        if (!$this->_selectorBlock) {
            $this->_selectorBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Urlrewrite\Selector');
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
     * @return \Magento\Core\Model\Url\Rewrite
     */
    protected function _getUrlRewrite()
    {
        if (!$this->hasData('url_rewrite')) {
            $this->setUrlRewrite($this->_rewriteFactory->create());
        }
        return $this->getUrlRewrite();
    }
}
