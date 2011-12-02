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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml tag edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Catalog_Search_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init Form properties
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('catalog_search_form');
        $this->setTitle(Mage::helper('Mage_Catalog_Helper_Data')->__('Search Information'));
    }

    /**
     * Prepare form fields
     *
     * @return Mage_Adminhtml_Block_Catalog_Search_Edit_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_catalog_search');
        /* @var $model Mage_CatalogSearch_Model_Query */

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('Mage_Catalog_Helper_Data')->__('General Information')));

        $yesno = array(
            array(
                'value' => 0,
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Yes')
            ));

        if ($model->getId()) {
            $fieldset->addField('query_id', 'hidden', array(
                'name'      => 'query_id',
            ));
        }

        $fieldset->addField('query_text', 'text', array(
            'name'      => 'query_text',
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Search Query'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Search Query'),
            'required'  => true,
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select', array(
                'name'      => 'store_id',
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Store'),
                'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Store'),
                'values'    => Mage::getSingleton('Mage_Adminhtml_Model_System_Store')->getStoreValuesForForm(true, false),
                'required'  => true,
            ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'      => 'store_id'
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        if ($model->getId()) {
            $fieldset->addField('num_results', 'text', array(
                'name'      => 'num_results',
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Number of results<br/>(For the last time placed)'),
                'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Number of results<br/>(For the last time placed)'),
                'required'  => true,
            ));

            $fieldset->addField('popularity', 'text', array(
                'name'      => 'popularity',
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Number of Uses'),
                'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Number of Uses'),
                'required'  => true,
            ));
        }

        $afterElementHtml = '<p class="nm"><small>'
            . Mage::helper('Mage_Catalog_Helper_Data')->__('(Will make search for the query above return results for this search.)')
            . '</small></p>';

        $fieldset->addField('synonym_for', 'text', array(
            'name'      => 'synonym_for',
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Synonym For'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Synonym For'),
            'after_element_html' => $afterElementHtml,
        ));

        $afterElementHtml = '<p class="nm"><small>'
            . Mage::helper('Mage_Catalog_Helper_Data')->__('ex. http://domain.com')
            . '</small></p>';

        $fieldset->addField('redirect', 'text', array(
            'name'      => 'redirect',
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Redirect URL'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Redirect URL'),
            'class'     => 'validate-url',
            'after_element_html' => $afterElementHtml,
        ));

        $fieldset->addField('display_in_terms', 'select', array(
            'name'      => 'display_in_terms',
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Display in Suggested Terms'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Display in Suggested Terms'),
            'values'    => $yesno,
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
