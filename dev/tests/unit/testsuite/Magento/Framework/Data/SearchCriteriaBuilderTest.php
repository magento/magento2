<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data;

class SearchCriteriaBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $interface = 'Magento\Framework\Api\CriteriaInterface';

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $factory = $this->getMock('Magento\Framework\Data\ObjectFactory', [], [], '', false);
        $builder = $objectManager->getObject(
            'Magento\Framework\Data\Stub\SearchCriteriaBuilder',
            ['objectFactory' => $factory]
        );
        $factory->expects($this->once())
            ->method('create')
            ->with($interface, ['queryBuilder' => $builder]);

        $builder->make();
    }
}
