<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\ObjectManagerInterface;

/**
 * Provide product template generator based on specified product type from fixture
 */
class ProductTemplateGeneratorFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $templateEntityMap = [
        Type::TYPE_SIMPLE => SimpleProductTemplateGenerator::class,
        BundleType::TYPE_CODE => BundleProductTemplateGenerator::class,
        Configurable::TYPE_CODE => ConfigurableProductTemplateGenerator::class,
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $fixture
     * @return TemplateEntityGeneratorInterface
     * @throws \InvalidArgumentException
     */
    public function create(array $fixture)
    {
        $type = isset($fixture['type_id']) ? $fixture['type_id'] : Type::TYPE_SIMPLE;
        if (!isset($this->templateEntityMap[$type])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot instantiate product template generator. Wrong type_id "%s" passed',
                $type
            ));
        }

        return $this->objectManager->create($this->templateEntityMap[$type], ['fixture' => $fixture]);
    }
}
