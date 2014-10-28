<?php
/**
 * Composite attribute property mapper
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Entity\Setup\PropertyMapper;

use Magento\Eav\Model\Entity\Setup\PropertyMapperInterface;
use Magento\Framework\ObjectManager;

class Composite implements PropertyMapperInterface
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $propertyMappers;

    /**
     * @param ObjectManager $objectManager
     * @param array $propertyMappers
     */
    public function __construct(ObjectManager $objectManager, array $propertyMappers = array())
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
        $data = array();
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
