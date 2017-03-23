<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductOptions;

class TypeList implements \Magento\Catalog\Api\ProductCustomOptionTypeListInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterfaceFactory
     */
    protected $factory;

    /**
     * @param Config $config
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterfaceFactory $factory
     */
    public function __construct(
        Config $config,
        \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterfaceFactory $factory
    ) {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $output = [];
        foreach ($this->config->getAll() as $option) {
            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                /** @var \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterface $optionType */
                $optionType = $this->factory->create();
                $optionType->setLabel(__($type['label']))
                    ->setCode($type['name'])
                    ->setGroup(__($option['label']));
                $output[] = $optionType;
            }
        }
        return $output;
    }
}
