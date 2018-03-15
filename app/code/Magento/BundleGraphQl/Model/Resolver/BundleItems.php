<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\BundleGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Bundle\Model\Product\Type;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Bundle\Model\OptionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * {@inheritdoc}
 */
class BundleItems implements ResolverInterface
{
    /**
     * @var OptionFactory
     */
    private $bundleOption;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param OptionFactory $bundleOption
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param StoreManagerInterface $storeManager
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        OptionFactory $bundleOption,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        StoreManagerInterface $storeManager,
        ValueFactory $valueFactory
    ) {
        $this->bundleOption = $bundleOption;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->storeManager = $storeManager;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Fetch and format bundle option items.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        if ($value['type_id'] !== Type::TYPE_CODE) {
            return null;
        }

        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $optionsCollection = $this->bundleOption->create()->getResourceCollection();
        // All products in collection will have same store id.
        $optionsCollection->joinValues($this->storeManager->getStore()->getId());
        $optionsCollection->setProductIdFilter($value['id']);
        $optionsCollection->setPositionOrder();

        $this->extensionAttributesJoinProcessor->process($optionsCollection);
        if (empty($optionsCollection->getData())) {
            return null;
        }

        $options = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionsCollection as $option) {
            $options[$option->getId()] = $option->getData();
            $options[$option->getId()]['title']
                = $option->getTitle() === null ? $option->getDefaultTitle() : $option->getTitle();
            $options[$option->getId()]['sku'] = $value['sku'];
        }

        $result = function () use ($options) {
            return $options;
        };

        return $this->valueFactory->create($result);
    }
}
