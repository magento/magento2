<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Bundle\Api\Data\BundleOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class ProductOptionProcessor implements ProductOptionProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var BundleOptionInterfaceFactory
     */
    protected $bundleOptionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param BundleOptionInterfaceFactory $bundleOptionFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        BundleOptionInterfaceFactory $bundleOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->bundleOptionFactory = $bundleOptionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(ProductOptionInterface $productOption)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $bundleOptions = $this->getBundleOptions($productOption);
        if (!empty($bundleOptions) && is_array($bundleOptions)) {
            $requestData = [];
            foreach ($bundleOptions as $option) {
                /** @var BundleOptionInterface $option */
                foreach ($option->getOptionSelections() as $selection) {
                    $requestData['bundle_option'][$option->getOptionId()][] = $selection;
                    $requestData['bundle_option_qty'][$option->getOptionId()] = $option->getOptionQty();
                }
            }
            $request->addData($requestData);
        }

        return $request;
    }

    /**
     * Retrieve bundle options
     *
     * @param ProductOptionInterface $productOption
     * @return array
     */
    protected function getBundleOptions(ProductOptionInterface $productOption)
    {
        if ($productOption
            && $productOption->getExtensionAttributes()
            && $productOption->getExtensionAttributes()->getBundleOptions()
        ) {
            return $productOption->getExtensionAttributes()
                ->getBundleOptions();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToProductOption(DataObject $request)
    {
        $bundleOptions = $request->getBundleOption();
        $bundleOptionsQty = $request->getBundleOptionQty();

        if (!empty($bundleOptions) && is_array($bundleOptions)) {
            $data = [];
            foreach ($bundleOptions as $optionId => $optionSelections) {
                if (empty($optionSelections)) {
                    continue;
                }
                $optionSelections = is_array($optionSelections) ? $optionSelections : [$optionSelections];
                $optionQty = isset($bundleOptionsQty[$optionId]) ? $bundleOptionsQty[$optionId] : 1;

                /** @var BundleOptionInterface $productOption */
                $productOption = $this->bundleOptionFactory->create();
                $productOption->setOptionId($optionId);
                $productOption->setOptionSelections($optionSelections);
                $productOption->setOptionQty($optionQty);
                $data[] = $productOption;
            }

            return ['bundle_options' => $data];
        }

        return [];
    }
}
