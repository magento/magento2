<?php
/**
 * Factory class for \Magento\Framework\Authorization
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization;

use Magento\Framework\Authorization;
use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * Entity class name
     */
    const CLASS_NAME = 'Magento\Framework\Authorization';

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Authorization
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(self::CLASS_NAME, $data);
    }
}
