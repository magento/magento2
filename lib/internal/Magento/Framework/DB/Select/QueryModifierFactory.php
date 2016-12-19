<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create instance of QueryModifierInterface
 */
class QueryModifierFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $queryModifiers;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $queryModifiers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $queryModifiers = []
    ) {
        $this->objectManager = $objectManager;
        $this->queryModifiers = $queryModifiers;
    }

    /**
     * Create instance of QueryModifierInterface
     *
     * @param string $type
     * @param array $data
     * @return QueryModifierInterface
     * @throws \InvalidArgumentException
     */
    public function create($type, array $data = [])
    {
        if (!isset($this->queryModifiers[$type])) {
            throw new \InvalidArgumentException('Unknown query modifier type ' . $type);
        }
        $queryModifier = $this->objectManager->create($this->queryModifiers[$type], $data);
        if (!($queryModifier instanceof QueryModifierInterface)) {
            throw new \InvalidArgumentException(
                $this->queryModifiers[$type] . ' must implement ' . QueryModifierInterface::class
            );
        }
        return $queryModifier;
    }
}
