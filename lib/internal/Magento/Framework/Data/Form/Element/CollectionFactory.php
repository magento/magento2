<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\Data\Form\Element\CollectionFactory
 *
 * @since 2.0.0
 */
class CollectionFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create collection factory with specified parameters
     *
     * @param array $data
     * @return Collection
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Framework\Data\Form\Element\Collection::class, $data);
    }
}
