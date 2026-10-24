<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Plugin\UserDataProvider;

use Magento\Contact\Plugin\UserDataProvider\ViewModel as ViewModelPlugin;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Unit test for the ViewModelPlugin class
 */
class ViewModelTest extends TestCase
{
    use MockCreationTrait;

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
        $this->viewModelMock = $this->createMock(ArgumentInterface::class);
        $this->blockMock = $this->createMock(DataObject::class);

        $this->plugin = new ViewModelPlugin($this->viewModelMock);
    }

    #[DataProvider('dataProvider')]
    public function testBeforeToHtml($hasDataResult, $setDataExpects)
    {
        $this->blockMock->expects($this->once())
            ->method('hasData')
            ->with('view_model')
            ->willReturn($hasDataResult);

        $invocationMatcher = $this->createInvocationMatcher($setDataExpects);

        $this->blockMock->expects($invocationMatcher)
            ->method('setData')
            ->with('view_model', $this->viewModelMock);

        $this->plugin->beforeToHtml($this->blockMock);
    }

    public static function dataProvider()
    {
        return [
            'view model was not preset before' => [
                'hasDataResult' => false,
                'setDataExpects' => 'once',
            ],
            'view model was pre-installed before' => [
                'hasDataResult' => true,
                'setDataExpects' => 'never',
            ]
        ];
    }
}
