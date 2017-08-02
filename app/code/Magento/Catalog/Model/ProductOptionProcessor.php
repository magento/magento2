<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\CustomOptions\CustomOptionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

/**
 * Class \Magento\Catalog\Model\ProductOptionProcessor
 *
 * @since 2.0.0
 */
class ProductOptionProcessor implements ProductOptionProcessorInterface
{
    /**
     * @var DataObjectFactory
     * @since 2.0.0
     */
    protected $objectFactory;

    /**
     * @var CustomOptionFactory
     * @since 2.0.0
     */
    protected $customOptionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Option\UrlBuilder
     * @since 2.1.0
     */
    private $urlBuilder;

    /**
     * @param DataObjectFactory $objectFactory
     * @param CustomOptionFactory $customOptionFactory
     * @since 2.0.0
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        CustomOptionFactory $customOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->customOptionFactory = $customOptionFactory;
    }

    /**
     * @inheritDoc
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function convertToProductOption(DataObject $request)
    {
        $options = $request->getOptions();
        if (!empty($options) && is_array($options)) {
            $data = [];
            foreach ($options as $optionId => $optionValue) {
                if (is_array($optionValue)) {
                    $optionValue = $this->processFileOptionValue($optionValue);
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

    /**
     * Returns option value with file built URL
     *
     * @param array $optionValue
     * @return array
     * @since 2.1.0
     */
    private function processFileOptionValue(array $optionValue)
    {
        if (array_key_exists('url', $optionValue) &&
            array_key_exists('route', $optionValue['url']) &&
            array_key_exists('params', $optionValue['url'])
        ) {
            $optionValue['url'] = $this->getUrlBuilder()->getUrl(
                $optionValue['url']['route'],
                $optionValue['url']['params']
            );
        }
        return $optionValue;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Option\UrlBuilder
     *
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getUrlBuilder()
    {
        if ($this->urlBuilder === null) {
            $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Product\Option\UrlBuilder::class);
        }
        return $this->urlBuilder;
    }
}
