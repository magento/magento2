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
namespace Magento\Catalog\Block\Adminhtml\Category\Tab;

class Design extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * @var array|null
     */
    protected $_category;

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * @return array|null
     */
    public function getCategory()
    {
        if (!$this->_category) {
            $this->_category = $this->_coreRegistry->registry('category');
        }
        return $this->_category;
    }

    /**
     * @return void
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Custom Design')));


        $this->_setFieldset($this->getCategory()->getDesignAttributes(), $fieldset);

        $form->addValues($this->getCategory()->getData());
        $form->setFieldNameSuffix('general');
        $this->setForm($form);
    }
}
