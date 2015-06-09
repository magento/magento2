<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Request;

use Magento\Framework\ObjectManager\TMap;

class BuilderComposite implements BuilderInterface
{
    /**
     * @var BuilderInterface[]
     */
    private $builders;

    /**
     * @param TMap $builders
     */
    public function __construct(
        TMap $builders
    ) {
        $this->builders = $builders;
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
