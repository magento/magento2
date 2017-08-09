<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Framework\App\ObjectManager;

/**
 * Class \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Actions
 *
 */
class Actions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     * @deprecated 100.1.0
     */
    protected $_sourceYesno;

    /**
     * @var string
     */
    protected $_nameInLayout = 'actions_apply_to';

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
     * @param \Magento\Rule\Block\Actions $ruleActions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     * @param \Magento\SalesRule\Model\RuleFactory|null $ruleFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = [],
        \Magento\SalesRule\Model\RuleFactory $ruleFactory = null
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_ruleActions = $ruleActions;
        $this->_sourceYesno = $sourceYesno;
        $this->ruleFactory = $ruleFactory ?: ObjectManager::getInstance()
            ->get(\Magento\SalesRule\Model\RuleFactory::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
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
        $model = $this->_coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of actions tab to supplied form.
     *
     * @param \Magento\SalesRule\Model\Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'actions_fieldset', $formName = 'sales_rule_form')
    {
        if (!$model) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->ruleFactory->create();
            $model->load($id);
        }

        $actionsFieldSetId = $model->getActionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'sales_rule/promo_quote/newActionHtml/form/' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $actionsFieldSetId
        );

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Apply the rule only to cart items matching the following conditions ' .
                    '(leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            [
                'name' => 'apply_to',
                'label' => __('Apply To'),
                'title' => __('Apply To'),
                'required' => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );

        $this->_eventManager->dispatch('adminhtml_block_salesrule_actions_prepareform', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setActionFormName($model->getActions(), $formName);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        return $form;
    }

    /**
     * Handles addition of form name to action and its actions.
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $actions
     * @param string $formName
     * @return void
     */
    private function setActionFormName(\Magento\Rule\Model\Condition\AbstractCondition $actions, $formName)
    {
        $actions->setFormName($formName);
        if ($actions->getActions() && is_array($actions->getActions())) {
            foreach ($actions->getActions() as $condition) {
                $this->setActionFormName($condition, $formName);
            }
        }
    }
}
