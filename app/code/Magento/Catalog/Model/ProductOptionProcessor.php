<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\CustomOptions\CustomOptionFactory;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class ProductOptionProcessor implements ProductOptionProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var CustomOptionFactory
     */
    protected $customOptionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param CustomOptionFactory $customOptionFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        CustomOptionFactory $customOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->customOptionFactory = $customOptionFactory;
    }

    /**
     * @inheritDoc
     */
    public function convertToBuyRequest(ProductOptionInterface $productOption)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $options = $this->getCustomOptions($productOption);
        if (!empty($options)) {
            $requestData = [];
            foreach ($options as $option) {
                $requestData['options'][$option->getOptionId()] = $option->getOptionValue();
            }
            $request->addData($requestData);
        }

        return $request;
    }

    /**
     * Retrieve custom options
     *
     * @param ProductOptionInterface $productOption
     * @return array
     */
    protected function getCustomOptions(ProductOptionInterface $productOption)
    {
        if ($productOption
            && $productOption->getExtensionAttributes()
            && $productOption->getExtensionAttributes()->getCustomOptions()
        ) {
            return $productOption->getExtensionAttributes()
                ->getCustomOptions();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function convertToProductOption(DataObject $request)
    {
        $options = $request->getOptions();
        if (!empty($options) && is_array($options)) {
            $data = [];
            foreach ($options as $optionId => $optionValue) {
                if (is_array($optionValue)) {
                    $optionValue = implode(',', $optionValue);
                }

                /** @var CustomOption $option */
                $option = $this->customOptionFactory->create();
                $option->setOptionId($optionId)->setOptionValue($optionValue);
                $data[] = $option;
            }

            return ['custom_options' => $data];
        }

        return [];
    }
}
