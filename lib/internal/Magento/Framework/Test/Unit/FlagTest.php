<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlagTest extends TestCase
{
    public function testConstruct()
    {
        $flagCode = 'synchronize';
        $flag = $this->createFlagInstance();
        $flag->setFlagCode($flagCode);
        $this->assertEquals($flagCode, $flag->getFlagCode());
    }

    public function testGetFlagDataJson()
    {
        $data = ['foo' => 'bar'];
        $serializedData = '{"foo":"bar"}';
        $flag = $this->createFlagInstance(['flag_code' => 'synchronize']);
        $this->assertNull($flag->getFlagData());
        $flag->setData('flag_data', $serializedData);
        $this->assertEquals($data, $flag->getFlagData());
    }

    public function testGetFlagDataSerialized()
    {
        $data = 'foo';
        $serializedData = 's:3:"foo";';
        $flag = $this->createFlagInstance(['flag_code' => 'synchronize']);
        $this->assertNull($flag->getFlagData());
        $flag->setData('flag_data', $serializedData);
        $this->assertEquals($data, $flag->getFlagData());
    }

    public function testSetFlagData()
    {
        $data = ['foo' => 'bar'];
        $serializedData = '{"foo":"bar"}';
        $flag = $this->createFlagInstance(['flag_code' => 'synchronize']);
        $flag->setFlagData($data);
        $this->assertEquals($serializedData, $flag->getData('flag_data'));
    }

    public function testLoadSelf()
    {
        $flag = $this->createFlagInstance(['flag_code' => 'synchronize']);
        $this->assertInstanceOf(Flag::class, $flag->loadSelf());
    }

    public function testLoadSelfException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please define flag code.');
        $flag = $this->createFlagInstance();
        $flag->loadSelf();
    }

    public function testBeforeSave()
    {
        $flagCode = 'synchronize';
        $flag = $this->createFlagInstance(['flag_code' => $flagCode]);
        $flag->setData('block', 'blockNmae');
        $this->assertSame($flag, $flag->save());
        $this->assertEquals($flagCode, $flag->getFlagCode());
    }

    public function testBeforeSaveException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please define flag code.');
        $flag = $this->createFlagInstance();
        $flag->setData('block', 'blockNmae');
        $flag->beforeSave();
    }

    /**
     * @param array $data
     * @return Flag
     */
    private function createFlagInstance(array $data = [])
    {
        $objectManager = new ObjectManager($this);
        $eventManagerMock = $this->createPartialMock(Manager::class, ['dispatch']);
        /** @var Context|MockObject $contextMock */
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $connectionMock->expects($this->any())
            ->method('beginTransaction')
            ->willReturnSelf();
        $appResource = $this->createMock(ResourceConnection::class);
        $appResource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $dbContextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $dbContextMock->expects($this->once())
            ->method('getResources')
            ->willReturn($appResource);
        $resourceMock = $this->getMockBuilder(FlagResource::class)
            ->onlyMethods(['__wakeup', 'load', 'save', 'addCommitCallback', 'commit', 'rollBack'])
            ->setConstructorArgs(['context' => $dbContextMock])
            ->getMock();

        $resourceMock->expects($this->any())
            ->method('addCommitCallback')
            ->willReturnSelf();
        return $objectManager->getObject(
            Flag::class,
            [
                'context' => $contextMock,
                'resource' => $resourceMock,
                'data' => $data,
                'json' => new Json(),
                'serialize' => new Serialize()
            ]
        );
    }
}
