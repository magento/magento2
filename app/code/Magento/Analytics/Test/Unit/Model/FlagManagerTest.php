<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\FlagManager;

class FlagManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var \Magento\Framework\FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->flagManagerMock = $this->getMockBuilder(\Magento\Framework\FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->flagManager = new FlagManager(
            $this->flagManagerMock
        );
    }

    public function testGetFlagData()
    {
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with('someCode');

        $this->flagManager->getFlagData('someCode');
    }

    public function testSaveFlag()
    {
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with('someCode', 'someValue');

        return $this->flagManager->saveFlag('someCode', 'someValue');
    }

    public function testDeleteFlag()
    {
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with('someCode');

        return $this->flagManager->deleteFlag('someCode');
    }
}
