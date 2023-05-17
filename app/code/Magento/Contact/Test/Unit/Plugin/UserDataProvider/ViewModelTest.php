<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Plugin\UserDataProvider;

use Magento\Contact\Plugin\UserDataProvider\ViewModel as ViewModelPlugin;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the ViewModelPlugin class
 */
class ViewModelTest extends TestCase
{
    /**
     * @var ArgumentInterface|MockObject
     */
    private $viewModelMock;

    /**
     * @var DataObject|MockObject
     */
    private $blockMock;

    /**
     * @var ViewModelPlugin
     */
    private $plugin;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->viewModelMock = $this->getMockForAbstractClass(ArgumentInterface::class);
        $this->blockMock = $this->createMock(DataObject::class);

        $this->plugin = new ViewModelPlugin($this->viewModelMock);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testBeforeToHtml($hasDataResult, $setDataExpects)
    {
        $this->blockMock->expects($this->once())
            ->method('hasData')
            ->with('view_model')
            ->willReturn($hasDataResult);

        $this->blockMock->expects($setDataExpects)
            ->method('setData')
            ->with('view_model', $this->viewModelMock);

        $this->plugin->beforeToHtml($this->blockMock);
    }

    public function dataProvider()
    {
        return [
            'view model was not preset before' => [
                'hasData' => false,
                'setData' => $this->once(),
            ],
            'view model was pre-installed before' => [
                'hasData' => true,
                'setData' => $this->never(),
            ]
        ];
    }
}
