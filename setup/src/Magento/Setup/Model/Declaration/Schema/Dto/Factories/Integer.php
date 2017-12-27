<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;

/**
 * Serves needs in integer digits. Default padding is 11.
 * Size is 4 byte.
 */
class Integer implements FactoryInterface
{
    /**
     * Describe default for different integer types
     *
     * @var array
     */
    private static $defaultPadding = [
        'int' => '11',
        'tinyint' => '2',
        'smallint' => '5',
        'bigint' => '20'
    ];

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
     * @param string                 $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Set default padding, like INTEGER(11)
     *
     * {@inheritdoc}
     *
     * @return array
     */
    public function create(array $data)
    {
        if (!isset($data['padding'])) {
            $data['padding'] = self::$defaultPadding[$data['type']];
        }
        //Auto increment field can`t be null
        if (isset($data['identity']) && $data['identity']) {
            $data['nullable'] = false;
        }

        if (isset($data['default'])) {
            $data['default'] = (int) $data['default'];
        }

        return $this->objectManager->create($this->className, $data);
    }
}
