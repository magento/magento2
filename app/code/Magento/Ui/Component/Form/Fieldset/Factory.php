<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Fieldset;

use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 *
 * @api
 */
class Factory
{
    /**
     * @var string
     */
    protected $className = Fieldset::class;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        protected readonly ObjectManagerInterface $objectManager
    ) {
    }

    /**
     * Create data provider
     *
     * @param array $arguments
     * @return Fieldset
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create($this->className, ['data' => $arguments]);
    }
}
