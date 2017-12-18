<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;

/**
 * Serves foreign key constraint needs.
 * By default primary key can have only one name - PRIMARY
 * And this name is hardcoded. This is done, in order to prevent creating 2 primary keys
 * for one table
 */
class Primary implements FactoryInterface
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
     * @param ObjectManagerInterface $objectManager
     * @param string $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * Set default padding, like INTEGER(11)
     *
     * {@inheritdoc}
     * @return array
     */
    public function create(array $data)
    {
        $data['name'] = Internal::PRIMARY_NAME;
        return $this->objectManager->create($this->className, $data);
    }
}
