<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

use Zend\Di\Di;
use Zend\Di\ServiceLocatorInterface;

/**
 * Factory class for @see \Magento\Setup\Model\Declaration\Schema\Dto\Structure
 */
class StructureFactory
{
    /**
     * @var Di
     */
    private $zendDi;

    /**
     * @var string
     */
    private $instanceName = Structure::class;

    /**
     * EntityFactory constructor.
     * @param Di $zendDi
     */
    public function __construct(Di $zendDi)
    {
        $this->zendDi = $zendDi;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Structure | object
     */
    public function create(array $data = [])
    {
        return $this->zendDi->newInstance($this->instanceName, $data);
    }
}
