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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml store edit form for website
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\System\Store\Edit\Form;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Website
    extends \Magento\Adminhtml\Block\System\Store\Edit\AbstractForm
{
    /**
     * @var \Magento\Core\Model\Store\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @param \Magento\Core\Model\Store\GroupFactory $groupFactory
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Store\GroupFactory $groupFactory,
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_groupFactory = $groupFactory;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
    }

    /**
     * Prepare website specific fieldset
     *
     * @param \Magento\Data\Form $form
     */
    protected function _prepareStoreFieldset(\Magento\Data\Form $form)
    {
        $websiteModel = $this->_coreRegistry->registry('store_data');
        $postData = $this->_coreRegistry->registry('store_post_data');
        if ($postData) {
            $websiteModel->setData($postData['website']);
        }
        $fieldset = $form->addFieldset('website_fieldset', array(
            'legend' => __('Web Site Information')
        ));
        /* @var $fieldset \Magento\Data\Form */

        $fieldset->addField('website_name', 'text', array(
            'name'      => 'website[name]',
            'label'     => __('Name'),
            'value'     => $websiteModel->getName(),
            'required'  => true,
            'disabled'  => $websiteModel->isReadOnly(),
        ));

        $fieldset->addField('website_code', 'text', array(
            'name'      => 'website[code]',
            'label'     => __('Code'),
            'value'     => $websiteModel->getCode(),
            'required'  => true,
            'disabled'  => $websiteModel->isReadOnly(),
        ));

        $fieldset->addField('website_sort_order', 'text', array(
            'name'      => 'website[sort_order]',
            'label'     => __('Sort Order'),
            'value'     => $websiteModel->getSortOrder(),
            'required'  => false,
            'disabled'  => $websiteModel->isReadOnly(),
        ));

        if ($this->_coreRegistry->registry('store_action') == 'edit') {
            $groups = $this->_groupFactory->create()->getCollection()
                ->addWebsiteFilter($websiteModel->getId())
                ->setWithoutStoreViewFilter()
                ->toOptionArray();

            $fieldset->addField('website_default_group_id', 'select', array(
                'name'      => 'website[default_group_id]',
                'label'     => __('Default Store'),
                'value'     => $websiteModel->getDefaultGroupId(),
                'values'    => $groups,
                'required'  => false,
                'disabled'  => $websiteModel->isReadOnly(),
            ));
        }

        if (!$websiteModel->getIsDefault() && $websiteModel->getStoresCount()) {
            $fieldset->addField('is_default', 'checkbox', array(
                'name'      => 'website[is_default]',
                'label'     => __('Set as Default'),
                'value'     => 1,
                'disabled'  => $websiteModel->isReadOnly(),
            ));
        } else {
            $fieldset->addField('is_default', 'hidden', array(
                'name'      => 'website[is_default]',
                'value'     => $websiteModel->getIsDefault()
            ));
        }

        $fieldset->addField('website_website_id', 'hidden', array(
            'name'  => 'website[website_id]',
            'value' => $websiteModel->getId()
        ));
    }
}
