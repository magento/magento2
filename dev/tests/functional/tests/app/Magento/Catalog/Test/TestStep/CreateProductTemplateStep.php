<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Mtf\TestStep\TestStepInterface;

/**
 * Create product attribute template using handler.
 */
class CreateProductTemplateStep implements TestStepInterface
{
    /**
     * CatalogAttributeSet fixture.
     *
     * @var string
     */
    protected $productTemplate;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param CatalogAttributeSet $productTemplate
     */
    public function __construct(CatalogAttributeSet $productTemplate)
    {
        $this->productTemplate = $productTemplate;
    }

    /**
     * Create product attribute template.
     *
     * @return array
     */
    public function run()
    {
        $this->productTemplate->persist();

        return ['productTemplate' => $this->productTemplate];
    }
}
