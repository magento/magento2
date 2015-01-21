<?php
/**
 * Composite attribute property mapper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Setup\PropertyMapper;

use Magento\Eav\Model\Entity\Setup\PropertyMapperInterface;
use Magento\Framework\ObjectManagerInterface;

class Composite implements PropertyMapperInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $propertyMappers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $propertyMappers
     */
    public function __construct(ObjectManagerInterface $objectManager, array $propertyMappers = [])
    {
        $this->objectManager = $objectManager;
        $this->propertyMappers = $propertyMappers;
    }

    /**
     * Map input attribute properties to storage representation
     *
     * @param array $input
     * @param int $entityTypeId
     * @return array
     * @throws \InvalidArgumentException
     */
    public function map(array $input, $entityTypeId)
    {
        $data = [];
        foreach ($this->propertyMappers as $class) {
            if (!is_subclass_of($class, '\Magento\Eav\Model\Entity\Setup\PropertyMapperInterface')) {
                throw new \InvalidArgumentException(
                    'Property mapper ' .
                    $class .
                    ' must' .
                    ' implement \Magento\Eav\Model\Entity\Setup\PropertyMapperInterface'
                );
            }
            $data = array_replace($data, $this->objectManager->get($class)->map($input, $entityTypeId));
        }
        return $data;
    }
}
