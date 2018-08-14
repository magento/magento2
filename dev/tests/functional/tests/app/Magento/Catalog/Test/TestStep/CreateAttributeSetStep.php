<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create product attribute template using handler.
 */
class CreateAttributeSetStep implements TestStepInterface
{
    /**
     * CatalogAttributeSet fixture.
     *
     * @var string
     */
    protected $attributeSet;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param CatalogAttributeSet $attributeSet
     */
    public function __construct(CatalogAttributeSet $attributeSet)
    {
        $this->attributeSet = $attributeSet;
    }

    /**
     * Create product attribute template.
     *
     * @return array
     */
    public function run()
    {
        $this->attributeSet->persist();

        return ['attributeSet' => $this->attributeSet];
    }
}
