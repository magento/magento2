<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Serves needs in integer digits. Default padding is 5.
 * Size is 2 bytes.
 */
class Smallinteger implements FactoryInterface
{
    /**
     * Default small integer padding
     */
    const DEFAULT_PADDING = "6";

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
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Set default padding, like SMALLINT(5)
     *
     * {@inheritdoc}
     * @return array
     */
    public function create(array $data)
    {
        if (!isset($data['padding'])) {
            $data['padding'] = self::DEFAULT_PADDING;
        }

        if (isset($data['default'])) {
            $data['default'] = (int) $data['default'];
        }

        return $this->objectManager->create($this->className, $data);
    }
}
