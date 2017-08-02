<?php
/**
 * Parser factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Inline;

/**
 * Class \Magento\Framework\Translate\Inline\ParserFactory
 *
 * @since 2.0.0
 */
class ParserFactory
{
    /**
     * Default instance type
     */
    const DEFAULT_INSTANCE_TYPE = \Magento\Framework\Translate\Inline\ParserInterface::class;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * Object constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Return instance of inline translate parser object
     *
     * @return \Magento\Framework\Translate\Inline\ParserInterface
     * @since 2.0.0
     */
    public function get()
    {
        return $this->_objectManager->get(self::DEFAULT_INSTANCE_TYPE);
    }

    /**
     * @param array $arguments
     * @return \Magento\Framework\Translate\Inline\ParserInterface
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(self::DEFAULT_INSTANCE_TYPE, $arguments);
    }
}
