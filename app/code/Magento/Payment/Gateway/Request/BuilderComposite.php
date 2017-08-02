<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Request;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Class BuilderComposite
 * @api
 * @since 2.0.0
 */
class BuilderComposite implements BuilderInterface
{
    /**
     * @var BuilderInterface[] | TMap
     * @since 2.0.0
     */
    private $builders;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $builders
     * @since 2.0.0
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $builders = []
    ) {
        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => BuilderInterface::class
            ]
        );
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @since 2.0.0
     */
    public function build(array $buildSubject)
    {
        $result = [];
        foreach ($this->builders as $builder) {
            // @TODO implement exceptions catching
            $result = $this->merge($result, $builder->build($buildSubject));
        }

        return $result;
    }

    /**
     * Merge function for builders
     *
     * @param array $result
     * @param array $builder
     * @return array
     * @since 2.0.0
     */
    protected function merge(array $result, array $builder)
    {
        return array_replace_recursive($result, $builder);
    }
}
