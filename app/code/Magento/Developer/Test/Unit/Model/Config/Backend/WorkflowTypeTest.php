<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Config\Backend;

use Magento\Framework\App\State;
use Magento\Framework\Model\Context;
use Magento\Developer\Model\Config\Backend\WorkflowType;
use Magento\Framework\App\State\CleanupFiles;
use Magento\Developer\Model\Config\Source\WorkflowType as SourceWorkflowType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class WorkflowTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WorkflowType
     */
    private $model;

    /**
     * @var State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $appStateMock;

    /**
     * @var CleanupFiles|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cleanerMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->appStateMock = $this->createMock(State::class);
        $this->objectManagerHelper = new ObjectManager($this);
        $contextArgs = $this->objectManagerHelper->getConstructArguments(
            Context::class,
            ['appState' => $this->appStateMock]
        );

        $this->cleanerMock = $this->createMock(CleanupFiles::class);

        $this->model = $this->objectManagerHelper->getObject(
            WorkflowType::class,
            [
                'context' => $this->objectManagerHelper->getObject(Context::class, $contextArgs),
                'cleaner' => $this->cleanerMock
            ]
        );

        parent::setUp();
    }

    /**
     */
    public function testBeforeSaveSwitchedToClientSideInProductionShouldThrowException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Client side compilation doesn\'t work in production mode');

        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $this->model->setValue(SourceWorkflowType::CLIENT_SIDE_COMPILATION);
        $this->model->beforeSave();
    }

    public function testAfterSaveValueIsChangedShouldCleanViewFiles()
    {
        $this->model->setValue(SourceWorkflowType::SERVER_SIDE_COMPILATION);
        $this->cleanerMock->expects($this->once())->method('clearMaterializedViewFiles');
        $this->model->afterSave();
    }
}
