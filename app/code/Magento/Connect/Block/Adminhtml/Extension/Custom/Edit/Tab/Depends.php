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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for Dependencies
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab;

class Depends
    extends \Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab\AbstractTab
{

    /**
     * Prepare Dependencies Form before rendering HTML
     *
     * @return \Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab\Package
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_depends');

        $fieldset = $form->addFieldset('depends_php_fieldset', array(
            'legend'    => __('PHP Version')
        ));

        $fieldset->addField('depends_php_min', 'text', array(
            'name'      => 'depends_php_min',
            'label'     => __('Minimum'),
            'required'  => true,
            'value'     => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION,
        ));

        $fieldset->addField('depends_php_max', 'text', array(
            'name'      => 'depends_php_max',
            'label'     => __('Maximum'),
            'required'  => true,
            'value'     => PHP_MAJOR_VERSION . '.' . (PHP_MINOR_VERSION + 1) . '.0',
        ));

        $form->setValues($this->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Retrieve list of loaded PHP extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        $extensions = array();
        foreach (get_loaded_extensions() as $ext) {
            $extensions[$ext] = $ext;
        }
        asort($extensions, SORT_STRING);
        return $extensions;
    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Dependencies');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Dependencies');
    }
}
