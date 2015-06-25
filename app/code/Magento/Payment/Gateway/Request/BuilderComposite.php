<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Request;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;

class BuilderComposite implements BuilderInterface
{
    /**
     * @var BuilderInterface[] | TMap
     */
    private $builders;

    /**
     * @param array $builders
     * @param TMapFactory $tmapFactory
     */
    public function __construct(
        array $builders,
        TMapFactory $tmapFactory
    ) {
        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => 'Magento\Payment\Gateway\Request\BuilderInterface'
            ]
        );
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $result = [];
        foreach ($this->builders as $builder) {
            // @TODO implement exceptions catching
            $result = array_merge($result, $builder->build($buildSubject));
        }
        return $result;
    }
}
