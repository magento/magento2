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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Helper\Product;

/**
 * Helper for fetching properties by product configurational item
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Configuration extends \Magento\App\Helper\AbstractHelper
    implements \Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $_config;

    /**
     * Filter manager
     *
     * @var \Magento\Filter\FilterManager
     */
    protected $filter;


    /**
     * Product option factory
     *
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $_productOptionFactory;

    /**
     * Magento string lib
     *
     * @var \Magento\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Filter\FilterManager $filter
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
     * @param \Magento\Stdlib\String $string
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Filter\FilterManager $filter,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $config,
        \Magento\Stdlib\String $string
    ) {
        $this->_productOptionFactory = $productOptionFactory;
        $this->filter = $filter;
        $this->_config = $config;
        $this->string = $string;
        parent::__construct($context);
    }

    /**
     * Retrieves product configuration options
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getCustomOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $options = array();
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $itemOption = $item->getOptionByCode('option_' . $option->getId());
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

                    $options[] = array(
                        'label' => $option->getTitle(),
                        'value' => $group->getFormattedOptionValue($itemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($itemOption->getValue()),
                        'option_id' => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView()
                    );
                }
            }
        }

        $addOptions = $item->getOptionByCode('additional_options');
        if ($addOptions) {
            $options = array_merge($options, unserialize($addOptions->getValue()));
        }

        return $options;
    }

    /**
     * Retrieves configuration options for configurable product
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     * @throws \Magento\Core\Exception
     */
    public function getConfigurableOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId != \Magento\Catalog\Model\Product\Type\Configurable::TYPE_CODE) {
             throw new \Magento\Core\Exception(__('The product type to extract configurable options is incorrect.'));
        }
        $attributes = $product->getTypeInstance()
            ->getSelectedAttributesInfo($product);
        return array_merge($attributes, $this->getCustomOptions($item));
    }

    /**
     * Retrieves configuration options for grouped product
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     * @throws \Magento\Core\Exception
     */
    public function getGroupedOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId != \Magento\Catalog\Model\Product\Type\Grouped::TYPE_CODE) {
             throw new \Magento\Core\Exception(__('The product type to extract configurable options is incorrect.'));
        }

        $options = array();
        /**
         * @var \Magento\Catalog\Model\Product\Type\Grouped
         */
        $typeInstance = $product->getTypeInstance();
        $associatedProducts = $typeInstance->getAssociatedProducts($product);

        if ($associatedProducts) {
            foreach ($associatedProducts as $associatedProduct) {
                $qty = $item->getOptionByCode('associated_product_' . $associatedProduct->getId());
                $option = array(
                    'label' => $associatedProduct->getName(),
                    'value' => ($qty && $qty->getValue()) ? $qty->getValue() : 0
                );

                $options[] = $option;
            }
        }

        $options = array_merge($options, $this->getCustomOptions($item));
        $isUnConfigured = true;
        foreach ($options as &$option) {
            if ($option['value']) {
                $isUnConfigured = false;
                break;
            }
        }
        return $isUnConfigured ? array() : $options;
    }

    /**
     * Retrieves product options list
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $typeId = $item->getProduct()->getTypeId();
        switch ($typeId) {
            case \Magento\Catalog\Model\Product\Type\Configurable::TYPE_CODE:
                return $this->getConfigurableOptions($item);
            case \Magento\Catalog\Model\Product\Type\Grouped::TYPE_CODE:
                return $this->getGroupedOptions($item);
            default:
                break;
        }
        return $this->getCustomOptions($item);
    }

    /**
     * Accept option value and return its formatted view
     *
     * @param mixed $optionValue
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
     */
    public function getFormattedOptionValue($optionValue, $params = null)
    {
        // Init params
        if (!$params) {
            $params = array();
        }
        $maxLength = isset($params['max_length']) ? $params['max_length'] : null;
        $cutReplacer = isset($params['cut_replacer']) ? $params['cut_replacer'] : '...';

        // Proceed with option
        $optionInfo = array();

        // Define input data format
        if (is_array($optionValue)) {
            if (isset($optionValue['option_id'])) {
                $optionInfo = $optionValue;
                if (isset($optionInfo['value'])) {
                    $optionValue = $optionInfo['value'];
                }
            } else if (isset($optionValue['value'])) {
                $optionValue = $optionValue['value'];
            }
        }

        // Render customized option view
        if (isset($optionInfo['custom_view']) && $optionInfo['custom_view']) {
            $_default = array('value' => $optionValue);
            if (isset($optionInfo['option_type'])) {
                try {
                    $group = $this->_productOptionFactory->create()->groupFactory($optionInfo['option_type']);
                    return array('value' => $group->getCustomizedView($optionInfo));
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
            return array('value' => $truncatedValue);
        } else {
            if ($maxLength) {
                $truncatedValue = $this->filter->truncate($optionValue, array('length' => $maxLength, 'etc' => ''));
            } else {
                $truncatedValue = $optionValue;
            }
            $truncatedValue = nl2br($truncatedValue);
        }

        $result = array('value' => $truncatedValue);

        if ($maxLength && ($this->string->strlen($optionValue) > $maxLength)) {
            $result['value'] = $result['value'] . $cutReplacer;
            $optionValue = nl2br($optionValue);
            $result['full_view'] = $optionValue;
        }

        return $result;
    }

    /**
     * Get allowed product types for configurable product
     *
     * @return \SimpleXMLElement
     */
    public function getConfigurableAllowedTypes()
    {
        $configData = $this->_config->getType('configurable');
        return isset($configData['allow_product_types']) ? $configData['allow_product_types'] : array();
    }
}
