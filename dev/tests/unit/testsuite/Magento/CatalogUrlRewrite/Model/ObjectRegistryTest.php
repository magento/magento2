<?php
/**
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
namespace Magento\CatalogUrlRewrite\Model;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Framework\Object;

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
