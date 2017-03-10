<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer;

use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;

class DataDifferenceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $type
     * @param array $arguments
     * @return DataDifferenceInterface
     * @throws InputException
     */
    public function create($type, array $arguments = [])
    {
        $diffMap = [
            'websites' => DataDifference\Websites::class,
            'stores' => DataDifference\Stores::class,
            'groups' => DataDifference\Groups::class,
        ];

        if (!isset($diffMap[$type])) {
            throw new InputException(__('Wrong data difference type: %1', $type));
        }

        return $this->objectManager->create($diffMap[$type], $arguments);
    }
}
