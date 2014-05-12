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
namespace Magento\Index\Block\Adminhtml\Process\Edit\Tab;

use Magento\Backend\Block\Widget\Form;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare form
     *
     * @return Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_index_process');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('index_process_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General'), 'class' => 'fieldset-wide'));

        $fieldset->addField('process_id', 'hidden', array('name' => 'process', 'value' => $model->getId()));

        $fieldset->addField(
            'name',
            'note',
            array(
                'label' => __('Index Name'),
                'title' => __('Index Name'),
                'text' => '<strong>' . $model->getIndexer()->getName() . '</strong>'
            )
        );

        $fieldset->addField(
            'description',
            'note',
            array(
                'label' => __('Index Description'),
                'title' => __('Index Description'),
                'text' => $model->getIndexer()->getDescription()
            )
        );

        $fieldset->addField(
            'mode',
            'select',
            array(
                'label' => __('Index Mode'),
                'title' => __('Index Mode'),
                'name' => 'mode',
                'value' => $model->getMode(),
                'values' => $model->getModesOptions()
            )
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Process Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Process Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return false
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return true
     */
    protected function _isAllowedAction($action)
    {
        return true;
    }
}
