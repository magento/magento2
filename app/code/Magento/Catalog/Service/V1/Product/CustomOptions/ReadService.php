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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Service\V1\Product\CustomOptions;

use Magento\Framework\Exception\NoSuchEntityException;

class ReadService implements \Magento\Catalog\Service\V1\Product\CustomOptions\ReadServiceInterface
{
    /**
     * Product Option Config
     *
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $productOptionConfig;

    /**
     * @var Data\OptionTypeBuilder
     */
    protected $optionTypeBuilder;

    /**
     * @var Data\OptionBuilder
     */
    protected $optionBuilder;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var Data\Option\Metadata\ReaderInterface
     */
    protected $optionMetadataReader;

    /**
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param Data\OptionTypeBuilder $optionTypeBuilder
     * @param Data\OptionBuilder $optionBuilder
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param Data\Option\Metadata\ReaderInterface $optionMetadataReader
     */
    public function __construct(
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        Data\OptionTypeBuilder $optionTypeBuilder,
        Data\OptionBuilder $optionBuilder,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        Data\Option\Metadata\ReaderInterface $optionMetadataReader
    ) {
        $this->productOptionConfig = $productOptionConfig;
        $this->optionTypeBuilder = $optionTypeBuilder;
        $this->optionBuilder = $optionBuilder;
        $this->productRepository = $productRepository;
        $this->optionMetadataReader = $optionMetadataReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        $output = [];
        foreach ($this->productOptionConfig->getAll() as $option) {
            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                $itemData = [
                    Data\OptionType::LABEL => __($type['label']),
                    Data\OptionType::CODE => $type['name'],
                    Data\OptionType::GROUP => __($option['label'])
                ];
                $output[] = $this->optionTypeBuilder->populateWithArray($itemData)->create();
            }
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productSku)
    {
        $product = $this->productRepository->get($productSku);
        $output = [];
        /** @var $option \Magento\Catalog\Model\Product\Option */
        foreach ($product->getOptions() as $option) {
            $output[] = $this->_createOptionDataObject($option);
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function get($productSku, $optionId)
    {
        $product = $this->productRepository->get($productSku);
        $option = $product->getOptionById($optionId);
        if ($option === null) {
            throw new NoSuchEntityException();
        }
        return $this->_createOptionDataObject($option);
    }

    /**
     * Create option data object
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option array
     */
    protected function _createOptionDataObject(\Magento\Catalog\Model\Product\Option $option)
    {
        $data = array(
            Data\Option::OPTION_ID => $option->getId(),
            Data\Option::TITLE => $option->getTitle(),
            Data\Option::TYPE => $option->getType(),
            Data\Option::IS_REQUIRE => $option->getIsRequire(),
            Data\Option::SORT_ORDER => $option->getSortOrder(),
            Data\Option::METADATA => $this->optionMetadataReader->read($option)
        );
        return $this->optionBuilder->populateWithArray($data)->create();
    }
}
