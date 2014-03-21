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
 * Class block for package
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab;

class Package extends \Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab\AbstractTab
{
    /**
     * Prepare Package Info Form before rendering HTML
     *
     * @return \Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab\Package
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_package');

        $fieldset = $form->addFieldset('package_fieldset', array('legend' => __('Package')));

        if ($this->getData('name') != $this->getData('file_name')) {
            $this->setData('file_name_disabled', $this->getData('file_name'));
            $fieldset->addField(
                'file_name_disabled',
                'text',
                array('name' => 'file_name_disabled', 'label' => __('Package File Name'), 'disabled' => 'disabled')
            );
        }

        $fieldset->addField('file_name', 'hidden', array('name' => 'file_name'));

        $fieldset->addField('name', 'text', array('name' => 'name', 'label' => __('Name'), 'required' => true));

        $fieldset->addField(
            'channel',
            'text',
            array('name' => 'channel', 'label' => __('Channel'), 'required' => true)
        );

        $versionsInfo = array(
            array('label' => __('1.5.0.0 & later'), 'value' => \Magento\Connect\Package::PACKAGE_VERSION_2X),
            array('label' => __('Pre-1.5.0.0'), 'value' => \Magento\Connect\Package::PACKAGE_VERSION_1X)
        );
        $fieldset->addField(
            'version_ids',
            'multiselect',
            array(
                'name' => 'version_ids',
                'required' => true,
                'label' => __('Supported releases'),
                'style' => 'height: 45px;',
                'values' => $versionsInfo
            )
        );

        $fieldset->addField(
            'summary',
            'textarea',
            array('name' => 'summary', 'label' => __('Summary'), 'style' => 'height:50px;', 'required' => true)
        );

        $fieldset->addField(
            'description',
            'textarea',
            array(
                'name' => 'description',
                'label' => __('Description'),
                'style' => 'height:200px;',
                'required' => true
            )
        );

        $fieldset->addField(
            'license',
            'text',
            array(
                'name' => 'license',
                'label' => __('License'),
                'required' => true,
                'value' => 'Open Software License (OSL 3.0)'
            )
        );

        $fieldset->addField(
            'license_uri',
            'text',
            array(
                'name' => 'license_uri',
                'label' => __('License URI'),
                'value' => 'http://opensource.org/licenses/osl-3.0.php'
            )
        );

        $form->setValues($this->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Package Info');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Package Info');
    }
}
