<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Serves foreign key constraint needs.
 * Add additonal onDelete param
 */
class Foreign implements FactoryInterface
{
    /**
     * Default padding number
     */
    const DEFAULT_ON_DELETE = "CASCADE";

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Set default on delete to foreign key
     *
     * {@inheritdoc}
     * @return array
     */
    public function create(array $data)
    {
        if (!isset($data['onDelete'])) {
            $data['onDelete'] = self::DEFAULT_ON_DELETE;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
