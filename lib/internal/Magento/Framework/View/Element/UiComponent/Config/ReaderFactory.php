<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ReaderFactory
 * @since 2.0.0
 */
class ReaderFactory
{
    const INSTANCE_NAME = \Magento\Framework\View\Element\UiComponent\Config\Reader::class;

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
     * Create config reader
     *
     * @param array $arguments
     * @return UiReaderInterface
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->objectManager->create(static::INSTANCE_NAME, $arguments);
    }
}
