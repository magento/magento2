<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\ObjectManagerInterface;

class SequenceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var SequenceRegistry
     */
    protected $sequenceRegistry;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @param SequenceRegistry $sequenceRegistry
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        SequenceRegistry $sequenceRegistry,
        ObjectManagerInterface $objectManager,
        $instanceName = 'Magento\\Framework\\Model\\Entity\\Sequence'
    ) {
        $this->sequenceRegistry = $sequenceRegistry;
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Creates sequence instance
     *
     * @param string $entityType
     * @param array $config
     * @return SequenceInterface
     */
    public function create($entityType, $config)
    {
        if ($this->sequenceRegistry->retrieve($entityType) === false) {
            if (isset($config[$entityType]['sequence'])) {
                $this->sequenceRegistry->register(
                    $entityType,
                    $config[$entityType]['sequence']
                );
            } elseif (isset($config[$entityType]['sequenceTable'])) {
                $this->sequenceRegistry->register(
                    $entityType,
                    $this->objectManager->create(
                        $this->instanceName,
                        [
                            'connectionName' => $config[$entityType]['connectionName'],
                            'sequenceTable' => $config[$entityType]['sequenceTable'],
                        ]
                    ),
                    $config[$entityType]['sequenceTable']
                );
            } else {
                $this->sequenceRegistry->register($entityType);
            }
        }
        return $this->sequenceRegistry->retrieve($entityType)['sequence'];
    }
}
