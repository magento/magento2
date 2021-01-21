<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Block\LayeredNavigation;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Helper\Media;
use Magento\Theme\Block\Html\Pager;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
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
     * @var AbstractFilter
     */
    protected $filter;

    /**
     * @var AttributeFactory
     */
    protected $layerAttribute;

    /**
     * @var Data
     */
    protected $swatchHelper;

    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * @var Pager
     */
    private $htmlPagerBlock;

    /**
     * @param Context $context
     * @param Attribute $eavAttribute
     * @param AttributeFactory $layerAttribute
     * @param Data $swatchHelper
     * @param Media $mediaHelper
     * @param array $data
     * @param Pager|null $htmlPagerBlock
     */
    public function __construct(
        Context $context,
        Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        Data $swatchHelper,
        Media $mediaHelper,
        array $data = [],
        ?Pager $htmlPagerBlock = null
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->layerAttribute = $layerAttribute;
        $this->swatchHelper = $swatchHelper;
        $this->mediaHelper = $mediaHelper;
        $this->htmlPagerBlock = $htmlPagerBlock ?? ObjectManager::getInstance()->get(Pager::class);

        parent::__construct($context, $data);
    }

    /**
     * Set filter and attribute objects
     *
     * @param \Magento\Catalog\Model\Layer\Filter\AbstractFilter $filter
     *
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
     * Get attribute swatch data
     *
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

        return [
            'attribute_id' => $this->eavAttribute->getId(),
            'attribute_code' => $this->eavAttribute->getAttributeCode(),
            'attribute_label' => $this->eavAttribute->getStoreLabel(),
            'options' => $attributeOptions,
            'swatches' => $swatches,
        ];
    }

    /**
     * Build filter option url
     *
     * @param string $attributeCode
     * @param int $optionId
     *
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        $query = [
            $attributeCode => $optionId,
            // exclude current page from urls
            $this->htmlPagerBlock->getPageVarName() => null
        ];

        return $this->_urlBuilder->getUrl(
            '*/*/*',
            [
                '_current' => true,
                '_use_rewrite' => true,
                '_query' => $query
            ]
        );
    }

    /**
     * Get view data for option with no results
     *
     * @param Option $swatchOption
     *
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
     * Get option data if visible
     *
     * @param FilterItem[] $filterItems
     * @param Option $swatchOption
     *
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
     * Get view data for option
     *
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     *
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
     * Check if option should be visible
     *
     * @param FilterItem $filterItem
     *
     * @return bool
     */
    protected function isOptionVisible(FilterItem $filterItem)
    {
        return !($this->isOptionDisabled($filterItem) && $this->isShowEmptyResults());
    }

    /**
     * Check if attribute values should be visible with no results
     *
     * @return bool
     */
    protected function isShowEmptyResults()
    {
        return $this->eavAttribute->getIsFilterable() != self::FILTERABLE_WITH_RESULTS;
    }

    /**
     * Check if option should be disabled
     *
     * @param FilterItem $filterItem
     *
     * @return bool
     */
    protected function isOptionDisabled(FilterItem $filterItem)
    {
        return !$filterItem->getCount();
    }

    /**
     * Retrieve filter item by id
     *
     * @param FilterItem[] $filterItems
     * @param integer $id
     *
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
     * Get swatch image path
     *
     * @param string $type
     * @param string $filename
     *
     * @return string
     */
    public function getSwatchPath($type, $filename)
    {
        return $this->mediaHelper->getSwatchAttributeImage($type, $filename);
    }
}
