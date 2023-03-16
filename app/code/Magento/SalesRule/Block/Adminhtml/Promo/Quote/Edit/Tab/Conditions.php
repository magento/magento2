<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\Form as FormData;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as BlockConditions;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\SalesRule\Model\RegistryConstants;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Block for rendering Conditions tab on Sales Rules creation page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Conditions extends Generic implements TabInterface
{
    /**
     * Core registry
     *
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var BlockConditions
     */
    protected $_conditions;

    /**
     * @var string
     */
    protected $_nameInLayout = 'conditions_apply_to';

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param BlockConditions $conditions
     * @param Fieldset $rendererFieldset
     * @param array $data
     * @param RuleFactory|null $ruleFactory
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        FormFactory $formFactory,
        BlockConditions $conditions,
        Fieldset $rendererFieldset,
        array $data = [],
        RuleFactory $ruleFactory = null
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->ruleFactory = $ruleFactory ?: ObjectManager::getInstance()
            ->get(RuleFactory::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     *
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
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
        $model = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of conditions tab to supplied form.
     *
     * @param Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return FormData
     * @throws LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'conditions_fieldset', $formName = 'sales_rule_form')
    {
        if (!$model) {
            $id = $this->getRequest()->getParam('id');
            $model = $this->ruleFactory->create();
            $model->load($id);
        }
        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'sales_rule/promo_quote/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var FormData $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->getLayout()->createBlock(Fieldset::class);
        $renderer->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $conditionsFieldSetId
        );

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all products).'
                )
            ]
        )->setRenderer(
            $renderer
        );
        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'           => 'conditions',
                'label'          => __('Conditions'),
                'title'          => __('Conditions'),
                'required'       => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_conditions
        );

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName);
        return $form;
    }

    /**
     * Handles addition of form name to condition and its conditions.
     *
     * @param AbstractCondition $conditions
     * @param string $formName
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, $formName)
    {
        $conditions->setFormName($formName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName);
            }
        }
    }
}
