<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\Framework\Object;
use Magento\TestFramework\Helper\ObjectManager;

class ObjectRegistryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogUrlRewrite\Model\ObjectRegistry */
    protected $objectRegistry;

    /** @var \Magento\Framework\Object|\PHPUnit_Framework_MockObject_MockObject */
    protected $object;

    protected function setUp()
    {
        $this->object = $this->getMock('Magento\Framework\Object');
        $this->object->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->objectRegistry = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\ObjectRegistry',
            ['entities' => [$this->object]]
        );
    }

    public function testGet()
    {
        $this->assertEquals($this->object, $this->objectRegistry->get(1));
    }

    public function testGetNotExistObject()
    {
        $this->assertEquals(null, $this->objectRegistry->get('no-id'));
    }

    public function testGetList()
    {
        $this->assertEquals([1 => $this->object], $this->objectRegistry->getList());
    }

    public function testGetEmptyList()
    {
        $objectRegistry = (new ObjectManager($this))->getObject(
            'Magento\CatalogUrlRewrite\Model\ObjectRegistry',
            ['entities' => []]
        );
        $this->assertEquals([], $objectRegistry->getList());
    }
}
