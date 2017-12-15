<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

class FlagTest extends \PHPUnit\Framework\TestCase
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
        $this->assertInstanceOf(\Magento\Framework\Flag::class, $flag->loadSelf());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please define flag code.
     */
    public function testLoadSelfException()
    {
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

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please define flag code.
     */
    public function testBeforeSaveException()
    {
        $flag = $this->createFlagInstance();
        $flag->setData('block', 'blockNmae');
        $flag->beforeSave();
    }

    /**
     * @param array $data
     * @return \Magento\Framework\Flag
     */
    private function createFlagInstance(array $data = [])
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $eventManagerMock = $this->createPartialMock(\Magento\Framework\Event\Manager::class, ['dispatch']);
        /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connectionMock->expects($this->any())
            ->method('beginTransaction')
            ->willReturnSelf();
        $appResource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $appResource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $dbContextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $dbContextMock->expects($this->once())
            ->method('getResources')
            ->willReturn($appResource);
        $resourceMock = $this->getMockBuilder(\Magento\Framework\Flag\FlagResource::class)
            ->setMethods(['__wakeup', 'load', 'save', 'addCommitCallback', 'commit', 'rollBack'])
            ->setConstructorArgs(['context' => $dbContextMock])
            ->getMock();

        $resourceMock->expects($this->any())
            ->method('addCommitCallback')
            ->willReturnSelf();
        return $objectManager->getObject(
            \Magento\Framework\Flag::class,
            [
                'context' => $contextMock,
                'resource' => $resourceMock,
                'data' => $data,
                'json' => new \Magento\Framework\Serialize\Serializer\Json(),
                'serialize' => new \Magento\Framework\Serialize\Serializer\Serialize()
            ]
        );
    }
}
