<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Test\Unit;

class TypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\EntityManager\TypeResolver
     */
    private $resolver;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resolver = new \Magento\Framework\EntityManager\TypeResolver();
    }

    /**
     * @param object $dataObject
     * @param string $interfaceNames
     * @dataProvider resolveDataProvider
     */
    public function testResolve($dataObject, $interfaceName)
    {
        $customerDataObject = $this->objectManager->getObject($dataObject);
        $this->assertEquals($interfaceName, $this->resolver->resolve($customerDataObject));
    }

    /**
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            [
                \Magento\Customer\Model\Data\Customer::class,
                \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            [
                \Magento\Catalog\Model\Category::class,
                \Magento\Catalog\Api\Data\CategoryInterface::class,
            ]
        ];
    }
}
