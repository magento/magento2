<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Block\Edit;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * URL rewrites edit form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends Generic
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
    protected $_formValues = [];

    /**
     * Adminhtml data
     *
     * @var BackendHelper
     */
    protected $_adminhtmlData = null;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var UrlRewriteFactory
     */
    protected $_rewriteFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param OptionProvider $optionProvider
     * @param UrlRewriteFactory $rewriteFactory
     * @param Store $systemStore
     * @param BackendHelper $adminhtmlData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        protected readonly OptionProvider $optionProvider,
        UrlRewriteFactory $rewriteFactory,
        Store $systemStore,
        BackendHelper $adminhtmlData,
        array $data = []
    ) {
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
     *
     * Set form data either from model values or from session
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $model = $this->_getModel();
        $this->_formValues = [
            'store_id' => $model->getStoreId(),
            'entity_type' => $model->getEntityType(),
            'entity_id' => $model->getEntityId(),
            'request_path' => $model->getRequestPath(),
            'target_path' => $model->getTargetPath(),
            'redirect_type' => $model->getRedirectType(),
            'description' => $model->getDescription(),
        ];

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
        /** @var FormData $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'use_container' => true, 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('URL Rewrite Information')]);

        $fieldset->addField(
            'entity_type',
            'hidden',
            [
                'name' => 'entity_type',
                'value' => $this->_formValues['entity_type']
            ]
        );

        $fieldset->addField(
            'entity_id',
            'hidden',
            [
                'name' => 'entity_id',
                'value' => $this->_formValues['entity_id']
            ]
        );

        $this->_prepareStoreElement($fieldset);

        $fieldset->addField(
            'request_path',
            'text',
            [
                'label' => __('Request Path'),
                'title' => __('Request Path'),
                'name' => 'request_path',
                'required' => true,
                'value' => $this->_formValues['request_path']
            ]
        );

        $fieldset->addField(
            'target_path',
            'text',
            [
                'label' => __('Target Path'),
                'title' => __('Target Path'),
                'name' => 'target_path',
                'required' => true,
                'disabled' => false,
                'value' => $this->_formValues['target_path']
            ]
        );

        $fieldset->addField(
            'redirect_type',
            'select',
            [
                'label' => __('Redirect Type'),
                'title' => __('Redirect Type'),
                'name' => 'redirect_type',
                'options' => $this->optionProvider->getOptions(),
                'value' => $this->_formValues['redirect_type']
            ]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'label' => __('Description'),
                'title' => __('Description'),
                'name' => 'description',
                'cols' => 20,
                'rows' => 5,
                'value' => $this->_formValues['description'],
                'wrap' => 'soft'
            ]
        );

        $this->setForm($form);
        $this->_formPostInit($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare store element
     *
     * @param Fieldset $fieldset
     * @return void
     */
    protected function _prepareStoreElement($fieldset)
    {
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
        } else {
            $storeElement = $fieldset->addField(
                'store_id',
                'select',
                [
                    'label' => __('Store'),
                    'title' => __('Store'),
                    'name' => 'store_id',
                    'required' => true,
                    'value' => $this->_formValues['store_id']
                ]
            );
            try {
                $stores = $this->_getStoresListRestrictedByEntityStores($this->_getEntityStores());
            } catch (LocalizedException $e) {
                $stores = [];
                $storeElement->setAfterElementHtml($e->getMessage());
            }
            $storeElement->setValues($stores);
            if ($this->_getModel()->getIsAutogenerated()) {
                $storeElement->setReadonly(true);
            }
            /** @var $renderer Element */
            $renderer = $this->getLayout()->createBlock(
                Element::class
            );
            $storeElement->setRenderer($renderer);
        }
    }

    /**
     * Form post init
     *
     * @param FormData $form
     * @return $this
     */
    protected function _formPostInit($form)
    {
        $form->setAction(
            $this->_adminhtmlData->getUrl('adminhtml/*/save', ['id' => $this->_getModel()->getId()])
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
        if ($this->_sessionData === null) {
            $this->_sessionData = $this->_backendSession->getData('url_rewrite_data', true);
        }
        return $this->_sessionData;
    }

    /**
     * Get URL rewrite model instance
     *
     * @return UrlRewrite
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
        if ($this->_allStores === null) {
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
     * Get stores list restricted by entity stores.
     * Stores should be filtered only if custom entity is specified.
     * If we use custom rewrite, all stores are accepted.
     *
     * @param array $entityStores
     * @return array
     */
    private function _getStoresListRestrictedByEntityStores(array $entityStores)
    {
        $stores = $this->_getAllStores();
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
