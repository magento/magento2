<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Grid\Massaction;

/**
 * Grid widget massaction block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @method \Magento\Quote\Model\Quote setHideFormElement(boolean $value) Hide Form element to prevent IE errors
 * @method boolean getHideFormElement()
 * @author      Magento Core Team <core@magentocommerce.com>
 * @TODO MAGETWO-31510: Remove deprecated class
 * @since 100.0.2
 */
class Extended extends \Magento\Backend\Block\Widget
{
    /**
     * Massaction items
     *
     * @var array
     */
    protected $_items = [];

    /**
     * Path to template file in theme
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/massaction_extended.phtml';

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Helper\Data $backendData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Helper\Data $backendData,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_backendData = $backendData;
        parent::__construct($context, $data);
    }

    /**
     * Sets Massaction template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setErrorText($this->escapeHtml(__('An item needs to be selected. Select and try again.')));
    }

    /**
     * Add new massaction item
     *
     * The item array should look like:
     * $item = array(
     *      'label'    => string,
     *      'complete' => string, // Only for ajax enabled grid (optional)
     *      'url'      => string,
     *      'confirm'  => string, // text of confirmation of this action (optional)
     *      'additional' => string|array|\Magento\Framework\View\Element\AbstractBlock // (optional)
     * );
     *
     * @param string $itemId
     * @param array $item
     * @return $this
     */
    public function addItem($itemId, array $item)
    {
        $this->_items[$itemId] = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Grid\Massaction\Item::class
        )->setData(
            $item
        )->setMassaction(
            $this
        )->setId(
            $itemId
        );

        if ($this->_items[$itemId]->getAdditional()) {
            $this->_items[$itemId]->setAdditionalActionBlock($this->_items[$itemId]->getAdditional());
            $this->_items[$itemId]->unsAdditional();
        }

        return $this;
    }

    /**
     * Retrieve massaction item with id $itemId
     *
     * @param string $itemId
     * @return \Magento\Backend\Block\Widget\Grid\Massaction\Item|null
     */
    public function getItem($itemId)
    {
        if (isset($this->_items[$itemId])) {
            return $this->_items[$itemId];
        }

        return null;
    }

    /**
     * Retrieve massaction items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Retrieve massaction items JSON
     *
     * @return string
     */
    public function getItemsJson()
    {
        $result = [];
        foreach ($this->getItems() as $itemId => $item) {
            $result[$itemId] = $item->toArray();
        }

        return $this->_jsonEncoder->encode($result);
    }

    /**
     * Retrieve massaction items count
     *
     * @return integer
     */
    public function getCount()
    {
        return sizeof($this->_items);
    }

    /**
     * Checks are massactions available
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->getCount() > 0 && $this->getParentBlock()->getMassactionIdField();
    }

    /**
     * Retrieve global form field name for all massaction items
     *
     * @return string
     */
    public function getFormFieldName()
    {
        return $this->getData('form_field_name') ? $this->getData('form_field_name') : 'massaction';
    }

    /**
     * Retrieve form field name for internal use. Based on $this->getFormFieldName()
     *
     * @return string
     */
    public function getFormFieldNameInternal()
    {
        return 'internal_' . $this->getFormFieldName();
    }

    /**
     * Retrieve massaction block js object name
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * Retrieve grid block js object name
     *
     * @return string
     */
    public function getGridJsObjectName()
    {
        return $this->getParentBlock()->getJsObjectName();
    }

    /**
     * Retrieve JSON string of selected checkboxes
     *
     * @return string
     */
    public function getSelectedJson()
    {
        if ($selected = $this->getRequest()->getParam($this->getFormFieldNameInternal())) {
            $selected = explode(',', $selected);
            return join(',', $selected);
        }
        return '';
    }

    /**
     * Retrieve array of selected checkboxes
     *
     * @return string[]
     */
    public function getSelected()
    {
        if ($selected = $this->getRequest()->getParam($this->getFormFieldNameInternal())) {
            $selected = explode(',', $selected);
            return $selected;
        }
        return [];
    }

    /**
     * Retrieve apply button html
     *
     * @return string
     */
    public function getApplyButtonHtml()
    {
        return $this->getButtonHtml(__('Submit'), $this->getJsObjectName() . ".apply()");
    }

    /**
     * @return string
     */
    public function getJavaScript()
    {
        return " {$this->getJsObjectName()} = new varienGridMassaction('{$this->getHtmlId()}', " .
            "{$this->getGridJsObjectName()}, '{$this->getSelectedJson()}'" .
            ", '{$this->getFormFieldNameInternal()}', '{$this->getFormFieldName()}');" .
            "{$this->getJsObjectName()}.setItems({$this->getItemsJson()}); " .
            "{$this->getJsObjectName()}.setGridIds('{$this->getGridIdsJson()}');" .
            ($this->getUseAjax() ? "{$this->getJsObjectName()}.setUseAjax(true);" : '') .
            ($this->getUseSelectAll() ? "{$this->getJsObjectName()}.setUseSelectAll(true);" : '') .
            "{$this->getJsObjectName()}.errorText = '{$this->getErrorText()}';" . "\n" .
            "window.{$this->getJsObjectName()} = {$this->getJsObjectName()};";
    }

    /**
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $allIdsCollection = clone $this->getParentBlock()->getCollection();

        if ($this->getMassactionIdField()) {
            $massActionIdField = $this->getMassactionIdField();
        } else {
            $massActionIdField = $this->getParentBlock()->getMassactionIdField();
        }

        $gridIds = $allIdsCollection->setPageSize(0)->getColumnValues($massActionIdField);

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getParentBlock()->getHtmlId() . '_massaction';
    }

    /**
     * Remove existing massaction item by its id
     *
     * @param string $itemId
     * @return $this
     */
    public function removeItem($itemId)
    {
        if (isset($this->_items[$itemId])) {
            unset($this->_items[$itemId]);
        }

        return $this;
    }

    /**
     * Retrieve select all functionality flag check
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseSelectAll()
    {
        return $this->_getData('use_select_all') === null || $this->_getData('use_select_all');
    }

    /**
     * Retrieve select all functionality flag check
     *
     * @param boolean $flag
     * @return $this
     */
    public function setUseSelectAll($flag)
    {
        $this->setData('use_select_all', (bool)$flag);
        return $this;
    }
}
