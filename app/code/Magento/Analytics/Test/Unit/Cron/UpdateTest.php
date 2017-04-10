<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Cron\Update;
use Magento\Analytics\Model\Connector;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;

/**
 * Class Update
 */
class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectorMock;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var Update
     */
    private $update;

    protected function setUp()
    {
        $this->connectorMock =  $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock =  $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagManagerMock =  $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reinitableConfigMock = $this->getMockBuilder(ReinitableConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->update = new Update(
            $this->connectorMock,
            $this->configWriterMock,
            $this->reinitableConfigMock,
            $this->flagManagerMock
        );
    }

    public function testExecute()
    {
        $this->connectorMock->expects($this->once())
            ->method('execute')
            ->with('update')
            ->willReturn(true);
        $this->configWriterMock->expects($this->once())
            ->method('delete')
            ->with(BaseUrlConfigPlugin::UPDATE_CRON_STRING_PATH);
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with(BaseUrlConfigPlugin::OLD_BASE_URL_FLAG_CODE);
        $this->reinitableConfigMock->expects($this->once())
            ->method('reinit');
        $this->assertTrue($this->update->execute());
    }

    public function testExecuteUnsuccess()
    {
        $this->connectorMock->expects($this->once())
            ->method('execute')
            ->with('update')
            ->willReturn(false);
        $this->assertFalse($this->update->execute());
    }
}
