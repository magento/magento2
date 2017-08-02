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
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $className = \Magento\Ui\Component\Form\Fieldset::class;

    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create data provider
     *
     * @param array $arguments
     * @return Fieldset
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create($this->className, ['data' => $arguments]);
    }
}
