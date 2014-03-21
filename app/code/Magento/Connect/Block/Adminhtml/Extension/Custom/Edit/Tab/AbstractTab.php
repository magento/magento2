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
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract for extension info tabs
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab;

use Magento\View\LayoutInterface;

abstract class AbstractTab extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var LayoutInterface[]
     */
    protected $_addRowButtonHtml;

    /**
     * @var LayoutInterface[]
     */
    protected $_removeRowButtonHtml;

    /**
     * @var LayoutInterface[]
     */
    protected $_addFileDepButtonHtml;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Connect\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Connect\Model\Session $session,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->setData($session->getCustomExtensionPackageFormData());
    }

    /**
     * TODO   remove ???
     *
     * @return $this
     */
    public function initForm()
    {
        return $this;
    }

    /**
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getValue($key, $default = '')
    {
        $value = $this->getData($key);
        return htmlspecialchars($value ? $value : $default);
    }

    /**
     * @param string $key
     * @param string $value
     * @return string
     */
    public function getSelected($key, $value)
    {
        return $this->getData($key) == $value ? 'selected="selected"' : '';
    }

    /**
     * @param string $key
     * @return string
     */
    public function getChecked($key)
    {
        return $this->getData($key) ? 'checked="checked"' : '';
    }

    /**
     * @param string $container
     * @param string $template
     * @param string $title
     * @return LayoutInterface[]
     */
    public function getAddRowButtonHtml($container, $template, $title = 'Add')
    {
        if (!isset($this->_addRowButtonHtml[$container])) {
            $this->_addRowButtonHtml[$container] = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setType(
                'button'
            )->setClass(
                'add'
            )->setLabel(
                __($title)
            )->setOnClick(
                "addRow('" . $container . "', '" . $template . "')"
            )->toHtml();
        }
        return $this->_addRowButtonHtml[$container];
    }

    /**
     * @param string $selector
     * @return LayoutInterface[]
     */
    public function getRemoveRowButtonHtml($selector = 'span')
    {
        if (!$this->_removeRowButtonHtml) {
            $this->_removeRowButtonHtml = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setType(
                'button'
            )->setClass(
                'delete'
            )->setLabel(
                __('Remove')
            )->setOnClick(
                "removeRow(this, '" . $selector . "')"
            )->toHtml();
        }
        return $this->_removeRowButtonHtml;
    }

    /**
     * @param string $selector
     * @param string $filesClass
     * @return LayoutInterface[]
     */
    public function getAddFileDepsRowButtonHtml($selector = 'span', $filesClass = 'files')
    {
        if (!$this->_addFileDepButtonHtml) {
            $this->_addFileDepButtonHtml = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setType(
                'button'
            )->setClass(
                'add'
            )->setLabel(
                __('Add files')
            )->setOnClick(
                "showHideFiles(this, '" . $selector . "', '" . $filesClass . "')"
            )->toHtml();
        }
        return $this->_addFileDepButtonHtml;
    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
