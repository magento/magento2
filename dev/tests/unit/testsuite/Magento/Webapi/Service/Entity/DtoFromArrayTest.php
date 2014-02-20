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

use Magento\Service\Entity\AbstractDto;
use Magento\Service\Entity\AbstractDtoTest;
use Magento\Webapi\Controller\ServiceArgsSerializer;

class DtoFromArrayTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceArgsSerializer */
    protected $serializer;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $typeProcessor = $objectManager->getObject('Magento\Webapi\Model\Config\ClassReflector\TypeProcessor');
        $this->serializer = new ServiceArgsSerializer($typeProcessor);
    }

    public function testSimpleProperties()
    {
        $data = ['entityId' => 15, 'name' => 'Test'];
        $result = $this->serializer
                       ->getInputData('\\Magento\\Webapi\\Service\\Entity\\TestService', 'simple', $data);
        $this->assertNotNull($result);
        $this->assertEquals(15, $result[0]);
        $this->assertEquals('Test', $result[1]);
    }

    public function testNestedDtoProperties()
    {
        $data = ['nested' => ['details' => ['entityId' => 15, 'name' => 'Test']]];
        $result = $this->serializer
            ->getInputData('\\Magento\\Webapi\\Service\\Entity\\TestService', 'nestedDto', $data);
        $this->assertNotNull($result);
        $this->assertTrue( $result[0] instanceof NestedDto);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        $this->assertNotEmpty($result[0]);
        /** @var NestedDto $arg */
        $arg = $result[0];
        $this->assertTrue($arg instanceof NestedDto);
        /** @var SimpleDto $details */
        $details = $arg->getDetails();
        $this->assertNotNull($details);
        $this->assertTrue($details instanceof SimpleDto);
        $this->assertEquals(15, $details->getEntityId());
        $this->assertEquals('Test', $details->getName());
    }

    public function testSimpleArrayProperties()
    {
        $data = ['ids'=>[1,2,3,4]];
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
        $data = ['associativeArray' => ['key' => 'value', 'key_two' => 'value_two']];
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

    public function testArrayOfDtoProperties()
    {
        $data = ['dtos' => [ ['entityId' => 14, 'name' => 'First'], [ 'entityId' => 15, 'name' => 'Second' ] ]];
        $result = $this->serializer->getInputData('\\Magento\\Webapi\\Service\\Entity\\TestService', 'dtoArray', $data);
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var array $dtos */
        $dtos = $result[0];
        $this->assertEquals(2, count($dtos));
        /** @var SimpleDto $first */
        $first = $dtos[0];
        /** @var SimpleDto $second */
        $second = $dtos[1];
        $this->assertTrue($first instanceof SimpleDto);
        $this->assertEquals(14, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof SimpleDto);
        $this->assertEquals(15, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }

    public function testNestedSimpleArrayProperties()
    {
        $data = ['arrayDto' => ['ids' => [1, 2, 3, 4]]];
        $result = $this->serializer
                       ->getInputData('\\Magento\\Webapi\\Service\\Entity\\TestService', 'nestedSimpleArray', $data);
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var SimpleArrayDto $dto */
        $dto = $result[0];
        $this->assertTrue($dto instanceof SimpleArrayDto);
        /** @var array $ids */
        $ids = $dto->getIds();
        $this->assertNotNull($ids);
        $this->assertEquals(4, count($ids));
        $this->assertEquals($data['arrayDto']['ids'], $ids);
    }

    public function testNestedAssociativeArrayProperties()
    {
        $data = ['associativeArrayDto' => ['associativeArray' => ['key' => 'value', 'key2' => 'value2']]];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedAssociativeArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var AssociativeArrayDto $dto */
        $dto = $result[0];
        $this->assertTrue($dto instanceof AssociativeArrayDto);
        /** @var array $associativeArray */
        $associativeArray = $dto->getAssociativeArray();
        $this->assertNotNull($associativeArray);
        $this->assertEquals('value', $associativeArray['key']);
        $this->assertEquals('value2', $associativeArray['key2']);
    }

    public function testNestedArrayOfDtoProperties()
    {
        $data = ['dtos' => ['items' => [['entityId' => 1, 'name' => 'First'], ['entityId' => 2, 'name' => 'Second']]]];
        $result = $this->serializer->getInputData(
            '\\Magento\\Webapi\\Service\\Entity\\TestService',
            'nestedDtoArray',
            $data
        );
        $this->assertNotNull($result);
        /** @var array $result */
        $this->assertEquals(1, count($result));
        /** @var DtoArrayDto $dtos */
        $dtos = $result[0];
        $this->assertTrue($dtos instanceof DtoArrayDto);
        /** @var array $items */
        $items = $dtos->getItems();
        $this->assertEquals(2, count($items));
        /** @var SimpleDto $first */
        $first = $items[0];
        /** @var SimpleDto $second */
        $second = $items[1];
        $this->assertTrue($first instanceof SimpleDto);
        $this->assertEquals(1, $first->getEntityId());
        $this->assertEquals('First', $first->getName());
        $this->assertTrue($second instanceof SimpleDto);
        $this->assertEquals(2, $second->getEntityId());
        $this->assertEquals('Second', $second->getName());
    }
}
