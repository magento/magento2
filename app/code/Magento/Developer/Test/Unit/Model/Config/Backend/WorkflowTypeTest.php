<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\State;
use Magento\Framework\Model\Context;

class WorkflowTypeTest extends \Magento\Framework\App\Test\Unit\Config\ValueTest
{
    /**
     * @var string
     */
    protected $class = 'Magento\Developer\Model\Config\Backend\WorkflowType';

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    protected function getArguments()
    {
        $this->appStateMock = $this->getMock(State::class, [], [], '', false);

        $arguments = parent::getArguments();
        $contextArgs = $this->objectManagerHelper->getConstructArguments(
            Context::class, ['appState' => $this->appStateMock]
        );
        $arguments['context'] = $this->objectManagerHelper->getObject(Context::class, $contextArgs);;
        return $arguments;
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Client side compilation doesn't work in production mode
     */
    public function testBeforeSaveSwitchedToClientSideInProductionShouldThrowException()
    {
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $this->model->setValue(\Magento\Developer\Model\Config\Source\WorkflowType::CLIENT_SIDE_COMPILATION);
        $this->model->beforeSave();
    }

    /**
     * @param int $callNumber
     * @param string $oldValue
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($callNumber, $oldValue)
    {
        $this->model->setValue(\Magento\Developer\Model\Config\Source\WorkflowType::CLIENT_SIDE_COMPILATION);
        parent::testAfterSave($callNumber, $oldValue);
    }
}