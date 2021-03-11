<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Developer\Block\Adminhtml\System\Config\WorkflowType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\State;

class WorkflowTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WorkflowType
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var AbstractElement|\PHPUnit\Framework\MockObject\MockObject
     */
    private $elementMock;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $appStateMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->elementMock = $this->createMock(AbstractElement::class);
        $this->appStateMock = $this->createMock(State::class);

        $contextArgs = $this->objectManagerHelper->getConstructArguments(
            Context::class,
            [
                'appState' => $this->appStateMock
            ]
        );

        $this->context = $this->objectManagerHelper->getObject(Context::class, $contextArgs);
        $this->model = $this->objectManagerHelper->getObject(WorkflowType::class, ['context' => $this->context]);
        parent::setUp();
    }

    /**
     * @param string $mode
     * @param int $disable
     * @dataProvider renderDataProvider
     */
    public function testRender($mode, $disable)
    {
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);

        $this->elementMock->expects($this->exactly($disable))->method('setReadonly')->with(true, true);
        $this->elementMock->expects($this->exactly($disable))
            ->method('addData')
            ->with(
                [
                    'can_use_website_value' => false,
                    'can_use_default_value' => false,
                    'can_restore_to_default' => false
                ]
            );

        $this->model->render($this->elementMock);
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return [
            [State::MODE_PRODUCTION, 1],
            [State::MODE_DEFAULT, 0],
            [State::MODE_DEVELOPER, 0]
        ];
    }
}
