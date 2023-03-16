<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset as FormRendererFieldset;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Labels extends Generic implements TabInterface
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * Initialize dependencies.
     *
     * @param TemplateContext $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleFactory $ruleFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @var string
     */
    protected $_nameInLayout = 'store_view_labels';

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Labels');
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Labels');
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $rule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);

        if (!$rule) {
            $id = $this->getRequest()->getParam('id');
            $rule = $this->ruleFactory->create();
            $rule->load($id);
        }

        /** @var FormData $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $labels = $rule->getStoreLabels();

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset = $this->_createStoreSpecificFieldset($form, $labels);
            if ($rule->isReadonly()) {
                foreach ($fieldset->getElements() as $element) {
                    $element->setReadonly(true, true);
                }
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Create store specific fieldset
     *
     * @param FormData $form
     * @param array $labels
     * @return Fieldset
     */
    protected function _createStoreSpecificFieldset($form, $labels)
    {
        $fieldset = $form->addFieldset(
            'store_labels_fieldset',
            ['legend' => __('Store View Specific Labels'), 'class' => 'store-scope']
        );
        $renderer = $this->getLayout()->createBlock(
            FormRendererFieldset::class
        );
        $fieldset->setRenderer($renderer);

        foreach ($this->_storeManager->getWebsites() as $website) {
            $fieldset->addField(
                "w_{$website->getId()}_label",
                'note',
                ['label' => $website->getName(), 'fieldset_html_class' => 'website']
            );
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField(
                    "sg_{$group->getId()}_label",
                    'note',
                    ['label' => $group->getName(), 'fieldset_html_class' => 'store-group']
                );
                foreach ($stores as $store) {
                    $fieldset->addField(
                        "s_{$store->getId()}",
                        'text',
                        [
                            'name' => 'store_labels[' . $store->getId() . ']',
                            'title' => $store->getName(),
                            'label' => $store->getName(),
                            'required' => false,
                            'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                            'fieldset_html_class' => 'store',
                            'data-form-part' => $this->getData('target_form')
                        ]
                    );
                }
            }
        }
        return $fieldset;
    }
}
