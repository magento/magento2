<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Block\Adminhtml\Promo\Widget;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Widget\Block\Adminhtml\Widget\Chooser as WidgetChooser;

/**
 * Widget that allows to select a sales rule.
 */
class Chooser extends GridExtended
{
    /**
     * @param TemplateContext $context
     * @param BackendHelper $backendHelper
     * @param RuleFactory $ruleFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        BackendHelper $backendHelper,
        protected readonly RuleFactory $ruleFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block constructor, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * Prepare rules collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->ruleFactory->create()->getResourceCollection();
        $this->setCollection($collection);

        $this->_eventManager->dispatch(
            'adminhtml_block_promo_widget_chooser_prepare_collection',
            ['collection' => $collection]
        );

        return parent::_prepareCollection();
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('sales_rule/promo_quote/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            WidgetChooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $rule = $this->ruleFactory->create()->load((int)$element->getValue());
            if ($rule->getId()) {
                $chooser->setLabel($this->escapeHtml($rule->getName()));
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var ruleName = trElement.down("td").next().innerHTML;
                var ruleId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                ' .
            $chooserJsObject .
            '.setElementValue(ruleId);
                ' .
            $chooserJsObject .
            '.setElementLabel(ruleName);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare columns for rules grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'rule_id',
            ['header' => __('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'rule_id']
        );

        $this->addColumn('name', ['header' => __('Rule'), 'align' => 'left', 'index' => 'name']);

        $this->addColumn(
            'coupon_code',
            ['header' => __('Coupon Code'), 'align' => 'left', 'width' => '150px', 'index' => 'code']
        );

        $this->addColumn(
            'from_date',
            [
                'header' => __('Start'),
                'align' => 'left',
                'width' => '120px',
                'type' => 'date',
                'index' => 'from_date'
            ]
        );

        $this->addColumn(
            'to_date',
            [
                'header' => __('End'),
                'align' => 'left',
                'width' => '120px',
                'type' => 'date',
                'default' => '--',
                'index' => 'to_date'
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'is_active',
                'type' => 'options',
                'options' => [1 => 'Active', 0 => 'Inactive']
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales_rule/promo_quote/chooser', ['_current' => true]);
    }
}
