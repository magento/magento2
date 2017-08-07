<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create instance of QueryModifierInterface
 * @since 2.2.0
 */
class QueryModifierFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @var array
     * @since 2.2.0
     */
    private $queryModifiers;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $queryModifiers
     * @since 2.2.0
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
     * @since 2.2.0
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
