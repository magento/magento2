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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tab for Content Management
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Content
    extends Mage_XmlConnect_Block_Adminhtml_Mobile_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_pages;

    /**
     * Class constructor
     * Setting view option
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * Add page input to fieldset
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param string $fieldPrefix
     */
    protected function _addPage($fieldset, $fieldPrefix)
    {
        $element = $fieldset->addField($fieldPrefix, 'page', array(
            'name'      => $fieldPrefix,
        ));
        $element->initFields(array(
            'name'      => $fieldPrefix,
            'values'    => $this->_pages,
        ));
    }

    /**
     * Prepare form before rendering HTML
     * Setting Form Fieldsets and fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication();
        $conf = $model->getConf();
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $pages = Mage::getResourceModel('Mage_XmlConnect_Model_Resource_Cms_Page_Collection')->toOptionIdArray();
        $dummy = array(array( 'value' => '', 'label' => '' ));
        $this->_pages = array_merge($dummy, $pages);

        $fieldset = $form->addFieldset('cms_pages', array('legend' => $this->__('Pages')));
        $this->_addElementTypes($fieldset);

        $fieldset->addField('page_row_add', 'addrow', array(
            'onclick' => 'insertNewTableRow(this)',
            'options' => $this->_pages,
            'class' => ' scalable save ',
            'label' => $this->__('Label'),
            'before_element_html' => $this->__('Get Content from CMS Page').'</td><td class="label">',
        ));

        if (!empty($conf['native']['pages'])) {
            foreach ($conf['native']['pages'] as $key=>$dummy) {
                $this->_addPage($fieldset, 'conf[native][pages]['.$key.']');
            }
        }

        $data = $model->getFormData();
        $data['page_row_add'] = $this->__('Add Page');
        $form->setValues($data);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Content');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Content');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return (bool) !Mage::getSingleton('Mage_Adminhtml_Model_Session')->getNewApplication();
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
}
