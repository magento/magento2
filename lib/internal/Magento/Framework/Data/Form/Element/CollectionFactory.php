<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\ObjectManagerInterface;

class CollectionFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
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
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Framework\Data\Form\Element\Collection::class, $data);
    }
}
