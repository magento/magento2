<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

/**
 * Class FlagTest
 *
 * @package Magento\Framework
 */
class FlagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Flag
     */
    protected $flag;

    protected function setUp()
    {
        $data = ['flag_code' => 'synchronize'];
        $this->createInstance($data);
    }

    /**
     * @param array $data
     */
    protected function createInstance(array $data = [])
    {
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', ['dispatch'], [], '', false, false);
        $context = $this->getMock('Magento\Framework\Model\Context', [], [], '', false, false);
        $context->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($eventManager));
        $registry = $this->getMock('Magento\Framework\Registry', [], [], '', false, false);

        $connection = $this->getMock(
            'Magento\Framework\DB\Adapter\Adapter',
            ['beginTransaction'],
            [],
            '',
            false,
            false
        );
        $connection->expects($this->any())
            ->method('beginTransaction')
            ->will($this->returnSelf());
        $appResource = $this->getMock(
            'Magento\Framework\App\ResourceConnection',
            [],
            [],
            '',
            false,
            false
        );
        $appResource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $dbContextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $dbContextMock->expects($this->once())->method('getResources')->willReturn($appResource);
        $resource = $this->getMock(
            '\Magento\Framework\Flag\FlagResource',
            ['__wakeup', 'load', 'save', 'addCommitCallback', 'commit', 'rollBack'],
            ['context' => $dbContextMock],
            '',
            true
        );
        $resource->expects($this->any())
            ->method('addCommitCallback')
            ->will($this->returnSelf());

        $resourceCollection = $this->getMockBuilder('Magento\Framework\Data\Collection\AbstractDb')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->flag = new \Magento\Framework\Flag(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function tearDown()
    {
        unset($this->flag);
    }

    public function testConstruct()
    {
        $flagCode = 'synchronize';
        $this->createInstance();
        $this->flag->setFlagCode('synchronize');
        $this->assertEquals($flagCode, $this->flag->getFlagCode());
    }

    public function testGetFlagData()
    {
        $result = $this->flag->getFlagData();
        $this->assertNull($result);
        $flagData = serialize('data');
        $this->flag->setData('flag_data', $flagData);
        $result = $this->flag->getFlagData();
        $this->assertEquals(unserialize($flagData), $result);
    }

    public function testSetFlagData()
    {
        $flagData = 'data';
        $this->flag->setFlagData($flagData);
        $result = unserialize($this->flag->getData('flag_data'));
        $this->assertEquals($flagData, $result);
    }

    public function testLoadSelf()
    {
        $this->assertInstanceOf('Magento\Framework\Flag', $this->flag->loadSelf());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please define flag code.
     */
    public function testLoadSelfException()
    {
        $this->createInstance();
        $this->flag->loadSelf();
    }

    public function testBeforeSave()
    {
        $this->flag->setData('block', 'blockNmae');
        $result = $this->flag->save();
        $this->assertSame($this->flag, $result);
        $this->assertEquals('synchronize', $this->flag->getFlagCode());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please define flag code.
     */
    public function testBeforeSaveException()
    {
        $this->createInstance();
        $this->flag->setData('block', 'blockNmae');
        $this->flag->beforeSave();
    }
}
