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
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Service\Data\AbstractExtensibleObject;
use Magento\Framework\Service\Data\AbstractExtensibleObjectTest;
use Magento\Webapi\Controller\ServiceArgsSerializer;

class DataFromArrayTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceArgsSerializer */
    protected $serializer;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $objectFactory = new \Magento\Webapi\Service\Entity\WebapiObjectManager($objectManager);
        $typeProcessor = $objectManager->getObject('Magento\Webapi\Model\Config\ClassReflector\TypeProcessor');
        $this->serializer = $objectManager->getObject(
            'Magento\Webapi\Controller\ServiceArgsSerializer',
            ['typeProcessor' => $typeProcessor, 'objectManager' => $objectFactory]
        );
    }

    public function testSimpleProperties()
    {
        $data = array('entityId' => 15, 'name' => 'Test');
        $result = $this->serializer->getInputData('\\Magento\\Webapi\\Service\\Entity\\TestService', 'simple', $data);
        $this->assertNotNull($result);
        $this->assertEquals(15, $result[0]);
        $this->assertEquals('Test', $result[1]);
    }

    public function testNestedDataProperties()
    {
        $data = array('nested' => array('details' => array('entityId' => 15, 'name' => 'Test')));
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedData',
            $data
        );
        $this->assertNotNull($result);
        $this->assertTrue($result[0] instanceof NestedData);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        $this->assertNotEmpty($result[0]);
        /** @var NestedData $arg */
        $arg = $result[0];
        $this->assertTrue($arg instanceof NestedData);
        /** @var SimpleData $details */
        $details = $arg->getDetails();
        $this->assertNotNull($details);
        $this->assertTrue($details instanceof SimpleData);
        $this->assertEquals(15, $details->getEntityId());
        $this->assertEquals('Test', $details->getName());
    }

    public function testSimpleArrayProperties()
    {
        $data = array('ids' => array(1, 2, 3, 4));
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'simpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $ids */
        $ids = $result[0];
        $this->assertNotNull($ids);
        $this->assertEquals(4, count($ids));
        $this->assertEquals($data['ids'], $ids);
    }

    public function testAssociativeArrayProperties()
    {
        $data = array('associativeArray' => array('key' => 'value', 'key_two' => 'value_two'));
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'associativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $associativeArray */
        $associativeArray = $result[0];
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value_two', $associativeArray['key_two']);
    }

    public function testArrayOfDataObjectProperties()
    {
        $data = array(
            'dataObjects' => array(
                array('entityId' => 14, 'name' => 'First'),
                array('entityId' => 15, 'name' => 'Second')
            )
        );
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'dataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $dataObjects */
        $dataObjects = $result[0];
        $this->assertEquals(2, count($dataObjects));
        /** @var SimpleData $first */
        $first = $dataObjects[0];
        /** @var SimpleData $second */
        $second = $dataObjects[1];
        $this->assertTrue($first instanceof SimpleData);
        $this->assertEquals(14, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof SimpleData);
        $this->assertEquals(15, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }

    public function testNestedSimpleArrayProperties()
    {
        $data = array('arrayData' => array('ids' => array(1, 2, 3, 4)));
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedSimpleArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var SimpleArrayData $dataObject */
        $dataObject = $result[0];
        $this->assertTrue($dataObject instanceof SimpleArrayData);
        /** @var array $ids */
        $ids = $dataObject->getIds();
        $this->assertNotNull($ids);
        $this->assertEquals(4, count($ids));
        $this->assertEquals($data['arrayData']['ids'], $ids);
    }

    public function testNestedAssociativeArrayProperties()
    {
        $data = array(
            'associativeArrayData' => array('associativeArray' => array('key' => 'value', 'key2' => 'value2'))
        );
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedAssociativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var AssociativeArrayData $dataObject */
        $dataObject = $result[0];
        $this->assertTrue($dataObject instanceof AssociativeArrayData);
        /** @var array $associativeArray */
        $associativeArray = $dataObject->getAssociativeArray();
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value2', $associativeArray['key2']);
    }

    public function testNestedArrayOfDataObjectProperties()
    {
        $data = array(
            'dataObjects' => array(
                'items' => array(array('entityId' => 1, 'name' => 'First'), array('entityId' => 2, 'name' => 'Second'))
            )
        );
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedDataArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var DataArrayData $dataObjects */
        $dataObjects = $result[0];
        $this->assertTrue($dataObjects instanceof DataArrayData);
        /** @var array $items */
        $items = $dataObjects->getItems();
        $this->assertEquals(2, count($items));
        /** @var SimpleData $first */
        $first = $items[0];
        /** @var SimpleData $second */
        $second = $items[1];
        $this->assertTrue($first instanceof SimpleData);
        $this->assertEquals(1, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof SimpleData);
        $this->assertEquals(2, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }
}
