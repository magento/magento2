<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\LayeredNavigation;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Framework\View\Element\Template;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RenderLayered extends Template
{
    /**
     * For `Filterable (with results)` setting
     */
    const FILTERABLE_WITH_RESULTS = '1';

    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'Magento_Swatches::product/layered/renderer.phtml';

    /**
     * @var \Magento\Eav\Model\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    protected $filter;

    /**
     * @var AttributeFactory
     */
    protected $layerAttribute;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * @var \Magento\Swatches\Helper\Media
     */
    protected $mediaHelper;

    /**
     * @param Template\Context $context
     * @param Attribute $eavAttribute
     * @param AttributeFactory $layerAttribute
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\Swatches\Helper\Media $mediaHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $mediaHelper,
        array $data = []
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->layerAttribute = $layerAttribute;
        $this->swatchHelper = $swatchHelper;
        $this->mediaHelper = $mediaHelper;

        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setSwatchFilter(\Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter)
    {
        $this->filter = $filter;
        $this->eavAttribute = $filter->getAttributeModel();

        return $this;
    }

    /**
     * @return array
     */
    public function getSwatchData()
    {
        if (false === $this->eavAttribute instanceof Attribute) {
            throw new \RuntimeException('Magento_Swatches: RenderLayered: Attribute has not been set.');
        }

        $attributeOptions = [];
        foreach ($this->eavAttribute->getOptions() as $option) {
            if ($currentOption = $this->getFilterOption($this->filter->getItems(), $option)) {
                $attributeOptions[$option->getValue()] = $currentOption;
            } elseif ($this->isShowEmptyResults()) {
                $attributeOptions[$option->getValue()] = $this->getUnusedOption($option);
            }
        }

        $attributeOptionIds = array_keys($attributeOptions);
        $swatches = $this->swatchHelper->getSwatchesByOptionsId($attributeOptionIds);

        $data = [
            'attribute_id' => $this->eavAttribute->getId(),
            'attribute_code' => $this->eavAttribute->getAttributeCode(),
            'attribute_label' => $this->eavAttribute->getStoreLabel(),
            'options' => $attributeOptions,
            'swatches' => $swatches,
        ];

        return $data;
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        $query = [$attributeCode => $optionId];
        return $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }

    /**
     * @param Option $swatchOption
     * @return array
     */
    protected function getUnusedOption(Option $swatchOption)
    {
        return [
            'label' => $swatchOption->getLabel(),
            'link' => 'javascript:void();',
            'custom_style' => 'disabled'
        ];
    }

    /**
     * @param FilterItem[] $filterItems
     * @param Option $swatchOption
     * @return array
     */
    protected function getFilterOption(array $filterItems, Option $swatchOption)
    {
        $resultOption = false;
        $filterItem = $this->getFilterItemById($filterItems, $swatchOption->getValue());
        if ($filterItem && $this->isOptionVisible($filterItem)) {
            $resultOption = $this->getOptionViewData($filterItem, $swatchOption);
        }

        return $resultOption;
    }

    /**
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     * @return array
     */
    protected function getOptionViewData(FilterItem $filterItem, Option $swatchOption)
    {
        $customStyle = '';
        $linkToOption = $this->buildUrl($this->eavAttribute->getAttributeCode(), $filterItem->getValue());
        if ($this->isOptionDisabled($filterItem)) {
            $customStyle = 'disabled';
            $linkToOption = 'javascript:void();';
        }

        return [
            'label' => $swatchOption->getLabel(),
            'link' => $linkToOption,
            'custom_style' => $customStyle
        ];
    }

    /**
     * @param FilterItem $filterItem
     * @return bool
     */
    protected function isOptionVisible(FilterItem $filterItem)
    {
        return $this->isOptionDisabled($filterItem) && $this->isShowEmptyResults() ? false : true;
    }

    /**
     * @return bool
     */
    protected function isShowEmptyResults()
    {
        return $this->eavAttribute->getIsFilterable() != self::FILTERABLE_WITH_RESULTS;
    }

    /**
     * @param FilterItem $filterItem
     * @return bool
     */
    protected function isOptionDisabled(FilterItem $filterItem)
    {
        return !$filterItem->getCount();
    }

    /**
     * @param FilterItem[] $filterItems
     * @param integer $id
     * @return bool|FilterItem
     */
    protected function getFilterItemById(array $filterItems, $id)
    {
        foreach ($filterItems as $item) {
            if ($item->getValue() == $id) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param string $type
     * @param string $filename
     * @return string
     */
    public function getSwatchPath($type, $filename)
    {
        $imagePath = $this->mediaHelper->getSwatchAttributeImage($type, $filename);

        return $imagePath;
    }
}
