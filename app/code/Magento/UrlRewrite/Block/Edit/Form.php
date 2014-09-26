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
namespace Magento\UrlRewrite\Block\Edit;

/**
 * URL rewrites edit form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var array
     */
    protected $_sessionData = null;

    /**
     * @var array
     */
    protected $_allStores = null;

    /**
     * @var bool
     */
    protected $_requireStoresFilter = false;

    /**
     * @var array
     */
    protected $_formValues = array();

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $_rewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Model\OptionProvider
     */
    protected $optionProvider;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\UrlRewrite\Model\OptionProvider $optionProvider
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\UrlRewrite\Model\OptionProvider $optionProvider,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = array()
    ) {
        $this->optionProvider = $optionProvider;
        $this->_rewriteFactory = $rewriteFactory;
        $this->_systemStore = $systemStore;
        $this->_adminhtmlData = $adminhtmlData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set form id and title
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('urlrewrite_form');
        $this->setTitle(__('Block Information'));
    }

    /**
     * Initialize form values
     * Set form data either from model values or from session
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $model = $this->_getModel();
        $this->_formValues = array(
            'store_id' => $model->getStoreId(),
            'entity_type' => $model->getEntityType(),
            'entity_id' => $model->getEntityId(),
            'request_path' => $model->getRequestPath(),
            'target_path' => $model->getTargetPath(),
            'redirect_type' => $model->getRedirectType(),
            'description' => $model->getDescription()
        );

        $sessionData = $this->_getSessionData();
        if ($sessionData) {
            foreach (array_keys($this->_formValues) as $key) {
                if (isset($sessionData[$key])) {
                    $this->_formValues[$key] = $sessionData[$key];
                }
            }
        }

        return $this;
    }

    /**
     * Prepare the form layout
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $this->_initFormValues();

        // Prepare form
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'use_container' => true, 'method' => 'post'))
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('URL Rewrite Information')));

        $fieldset->addField(
            'entity_type',
            'hidden',
            array(
                'name' => 'entity_type',
                'value' => $this->_formValues['entity_type']
            )
        );

        $fieldset->addField(
            'entity_id',
            'hidden',
            array(
                'name' => 'entity_id',
                'value' => $this->_formValues['entity_id']
            )
        );

        $this->_prepareStoreElement($fieldset);

        $fieldset->addField(
            'request_path',
            'text',
            array(
                'label' => __('Request Path'),
                'title' => __('Request Path'),
                'name' => 'request_path',
                'required' => true,
                'value' => $this->_formValues['request_path']
            )
        );

        $fieldset->addField(
            'target_path',
            'text',
            array(
                'label' => __('Target Path'),
                'title' => __('Target Path'),
                'name' => 'target_path',
                'required' => true,
                'disabled' => false,
                'value' => $this->_formValues['target_path']
            )
        );

        $fieldset->addField(
            'redirect_type',
            'select',
            array(
                'label' => __('Redirect Type'),
                'title' => __('Redirect Type'),
                'name' => 'redirect_type',
                'options' => $this->optionProvider->toOptionArray(),
                'value' => $this->_formValues['redirect_type']
            )
        );

        $fieldset->addField(
            'description',
            'textarea',
            array(
                'label' => __('Description'),
                'title' => __('Description'),
                'name' => 'description',
                'cols' => 20,
                'rows' => 5,
                'value' => $this->_formValues['description'],
                'wrap' => 'soft'
            )
        );

        $this->setForm($form);
        $this->_formPostInit($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare store element
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @return void
     */
    protected function _prepareStoreElement($fieldset)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array('name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId())
            );
        } else {
            /** @var $renderer \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element */
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );

            $storeElement = $fieldset->addField(
                'store_id',
                'select',
                array(
                    'label' => __('Store'),
                    'title' => __('Store'),
                    'name' => 'store_id',
                    'required' => true,
                    'values' => $this->_getRestrictedStoresList(),
                    'value' => $this->_formValues['store_id']
                )
            );
            if ($this->_getModel()->getIsAutogenerated()) {
                $storeElement->setReadonly(true);
            }
            $storeElement->setRenderer($renderer);
        }
    }

    /**
     * Form post init
     *
     * @param \Magento\Framework\Data\Form $form
     * @return $this
     */
    protected function _formPostInit($form)
    {
        $form->setAction(
            $this->_adminhtmlData->getUrl('adminhtml/*/save', array('id' => $this->_getModel()->getId()))
        );
        return $this;
    }

    /**
     * Get session data
     *
     * @return array
     */
    protected function _getSessionData()
    {
        if (is_null($this->_sessionData)) {
            $this->_sessionData = $this->_backendSession->getData('url_rewrite_data', true);
        }
        return $this->_sessionData;
    }

    /**
     * Get URL rewrite model instance
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected function _getModel()
    {
        if (!$this->hasData('url_rewrite')) {
            $this->setUrlRewrite($this->_rewriteFactory->create());
        }
        return $this->getUrlRewrite();
    }

    /**
     * Get request stores
     *
     * @return array
     */
    protected function _getAllStores()
    {
        if (is_null($this->_allStores)) {
            $this->_allStores = $this->_systemStore->getStoreValuesForForm();
        }

        return $this->_allStores;
    }

    /**
     * Get entity stores
     *
     * @return array
     */
    protected function _getEntityStores()
    {
        return $this->_getAllStores();
    }

    /**
     * Get restricted stores list
     * Stores should be filtered only if custom entity is specified.
     * If we use custom rewrite, all stores are accepted.
     *
     * @return array
     */
    protected function _getRestrictedStoresList()
    {
        $stores = $this->_getAllStores();
        $entityStores = $this->_getEntityStores();
        $stores = $this->_getStoresListRestrictedByEntityStores($stores, $entityStores);

        return $stores;
    }

    /**
     * Get stores list restricted by entity stores
     *
     * @param array $stores
     * @param array $entityStores
     * @return array
     */
    private function _getStoresListRestrictedByEntityStores(array $stores, array $entityStores)
    {
        if ($this->_requireStoresFilter) {
            foreach ($stores as $i => $store) {
                if (isset($store['value']) && $store['value']) {
                    $found = false;
                    foreach ($store['value'] as $k => $v) {
                        if (isset($v['value']) && in_array($v['value'], $entityStores)) {
                            $found = true;
                        } else {
                            unset($stores[$i]['value'][$k]);
                        }
                    }
                    if (!$found) {
                        unset($stores[$i]);
                    }
                }
            }
        }

        return $stores;
    }
}
