<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Timestamp DTO element factory.
 *
 * This format is used to save date (year, month, day).
 * Probably your SQL engine will save date in this format: 'YYYY-MM-DD HH:MM::SS'
 * Date time in invalid format will be converted to '0000-00-00 00:00:00' string
 * MySQL timestamp is similar to UNIX timestamp. You can pass you local time there and it will
 * be converted to UTC timezone. Then when you will try to pull your time back it will be converted
 * to your local time again.
 * Unix range: 1970-01-01 00:00:01' UTC to '2038-01-09 03:14:07'
 */
class Timestamp implements FactoryInterface
{
    /**
     * Nullable timestamp value.
     */
    const NULL_TIMESTAMP = 'NULL';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param BooleanUtils           $booleanUtils
     * @param string                 $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        BooleanUtils $booleanUtils,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Timestamp::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $data['onUpdate'] = isset($data['on_update']) ? $data['on_update'] : null;
        //OnUpdate is boolean as there is only one possible value for onUpdate statement.
        if ($data['onUpdate'] && $data['onUpdate'] !== 'CURRENT_TIMESTAMP') {
            if ($this->booleanUtils->toBoolean($data['onUpdate'])) {
                $data['onUpdate'] = 'CURRENT_TIMESTAMP';
            } else {
                unset($data['onUpdate']);
            }
        }
        //By default default attribute is not used.
        if (!isset($data['default'])) {
            $data['default'] = self::NULL_TIMESTAMP;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
