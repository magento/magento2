<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Helper for fetching properties by product configurational item
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Configuration extends AbstractHelper implements ConfigurationInterface
{
    /**
     * Filter manager
     *
     * @var FilterManager
     */
    protected $filter;

    /**
     * Product option factory
     *
     * @var OptionFactory
     */
    protected $_productOptionFactory;

    /**
     * Magento string lib
     *
     * @var StringUtils
     */
    protected $string;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param OptionFactory $productOptionFactory
     * @param FilterManager $filter
     * @param StringUtils $string
     * @param Json $serializer
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        OptionFactory $productOptionFactory,
        FilterManager $filter,
        StringUtils $string,
        Json $serializer,
        Escaper $escaper
    ) {
        $this->_productOptionFactory = $productOptionFactory;
        $this->filter = $filter;
        $this->string = $string;
        $this->serializer = $serializer;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Retrieves product configuration options
     *
     * @param ItemInterface $item
     *
     * @return array
     */
    public function getCustomOptions(ItemInterface $item)
    {
        $product = $item->getProduct();
        $options = [];
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            $options = [];
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if (!$option) {
                    continue;
                }

                $itemOption = $item->getOptionByCode('option_' . $option->getId());
                /** @var $group DefaultType */
                $group = $option->groupFactory($option->getType())
                    ->setOption($option)
                    ->setConfigurationItem($item)
                    ->setConfigurationItemOption($itemOption);

                if ('file' == $option->getType()) {
                    $downloadParams = $item->getFileDownloadParams();
                    if ($downloadParams) {
                        $url = $downloadParams->getUrl();
                        if ($url) {
                            $group->setCustomOptionDownloadUrl($url);
                        }
                        $urlParams = $downloadParams->getUrlParams();
                        if ($urlParams) {
                            $group->setCustomOptionUrlParams($urlParams);
                        }
                    }
                }

                $options[] = [
                    'label' => $option->getTitle(),
                    'value' => $group->getFormattedOptionValue($itemOption->getValue()),
                    'print_value' => $group->getPrintableOptionValue($itemOption->getValue()),
                    'option_id' => $option->getId(),
                    'option_type' => $option->getType(),
                    'custom_view' => $group->isCustomizedView(),
                ];
            }
        }

        $addOptions = $item->getOptionByCode('additional_options');
        if ($addOptions) {
            $options = array_merge($options, $this->serializer->unserialize($addOptions->getValue()));
        }

        return $options;
    }

    /**
     * Retrieves product options list
     *
     * @param ItemInterface $item
     *
     * @return array
     */
    public function getOptions(ItemInterface $item)
    {
        return $this->getCustomOptions($item);
    }

    /**
     * Accept option value and return its formatted view
     *
     * @param string|array $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     * @param array $params
     * All keys are options. Following supported:
     *  - 'maxLength': truncate option value if needed, default: do not truncate
     *  - 'cutReplacer': replacer for cut off value part when option value exceeds maxLength
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getFormattedOptionValue($optionValue, $params = null)
    {
        // Init params
        if (!$params) {
            $params = [];
        }
        $maxLength = $params['max_length'] ?? null;
        $cutReplacer = $params['cut_replacer'] ?? '...';

        // Proceed with option
        $optionInfo = [];

        // Define input data format
        if (is_array($optionValue)) {
            if (isset($optionValue['option_id'])) {
                $optionInfo = $optionValue;
                if (isset($optionInfo['value'])) {
                    $optionValue = $this->escaper->escapeHtml($optionInfo['value']);
                }
            } elseif (isset($optionValue['value'])) {
                $optionValue = $optionValue['value'];
            }
        }

        // Render customized option view
        if (isset($optionInfo['custom_view']) && $optionInfo['custom_view']) {
            $_default = ['value' => $optionValue];
            if (isset($optionInfo['option_type'])) {
                try {
                    $group = $this->_productOptionFactory->create()->groupFactory($optionInfo['option_type']);
                    return ['value' => $group->getCustomizedView($optionInfo)];
                } catch (\Exception $e) {
                    return $_default;
                }
            }
            return $_default;
        }

        // Truncate standard view
        if (is_array($optionValue)) {
            $truncatedValue = implode("\n", $optionValue);
            $truncatedValue = nl2br($truncatedValue);
            return ['value' => $truncatedValue];
        }

        if ($maxLength) {
            $truncatedValue = $this->filter->truncate($optionValue, ['length' => $maxLength, 'etc' => '']);
        } else {
            $truncatedValue = $optionValue;
        }
        $truncatedValue = nl2br($truncatedValue);

        $result = ['value' => $truncatedValue];

        if ($maxLength && $this->string->strlen($optionValue) > $maxLength) {
            $result['value'] .= $cutReplacer;
            $optionValue = nl2br($optionValue);
            $result['full_view'] = $optionValue;
        }

        return $result;
    }
}
