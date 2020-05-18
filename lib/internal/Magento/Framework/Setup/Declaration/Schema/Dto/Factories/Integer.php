<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\ObjectManagerInterface;

/**
 * Integer DTO element factory.
 */
class Integer implements FactoryInterface
{
    /**
     * Describe default for different integer types.
     *
     * @var array
     */
    private static $defaultPadding = [
        'int' => '11',
        'tinyint' => '2',
        'smallint' => '6',
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
     * @var SqlVersionProvider
     */
    private $sqlVersionProvider;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface    $objectManager
     * @param string                    $className
     * @param SqlVersionProvider|null   $sqlVersionProvider
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer::class,
        SqlVersionProvider $sqlVersionProvider = null
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->sqlVersionProvider = $sqlVersionProvider ?: $objectManager->get(SqlVersionProvider::class);
    }

    /**
     * @inheritdoc
     */
    public function create(array $data)
    {
        if (isset($data['padding'])) {
            unset($data['padding']);
        }
        if ($this->sqlVersionProvider->getSqlVersion() !== SqlVersionProvider::MYSQL_8_VERSION) {
            $data['padding'] = self::$defaultPadding[$data['type']];
        }

        //Auto increment field can`t be null
        if (isset($data['identity']) && $data['identity']) {
            $data['nullable'] = false;
        }

        if (isset($data['default'])) {
            $data['default'] = $data['default'] !== 'null' ? (int) $data['default'] : null;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
