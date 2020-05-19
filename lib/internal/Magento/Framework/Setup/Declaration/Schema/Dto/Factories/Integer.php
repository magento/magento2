<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto\Factories;

use Magento\Framework\DB\Adapter\SqlVersionProvider;
use Magento\Framework\ObjectManagerInterface;

/**
 * Integer DTO element factory.
 */
class Integer implements FactoryInterface
{
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
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Integer::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * @inheritdoc
     */
    public function create(array $data)
    {
        unset($data['padding']);

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
