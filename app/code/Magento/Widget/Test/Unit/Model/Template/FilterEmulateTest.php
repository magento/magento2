<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Test\Unit\Model\Template;

use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Widget\Model\Template\FilterEmulate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterEmulateTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var FilterEmulate
     */
    protected $filterEmulate;

    /**
     * @var State|MockObject
     */
    protected $appStateMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->appStateMock = $this->createMock(State::class);

        $this->filterEmulate = $this->objectManagerHelper->getObject(
            FilterEmulate::class,
            ['appState' => $this->appStateMock]
        );
    }

    /**
     * @return void
     */
    public function testWidgetDirective()
    {
        $result = 'some text';
        $construction = [
            '{{widget type="Widget\\Link" anchor_text="Test" template="block.phtml" id_path="p/1"}}',
            'widget',
            ' type="" anchor_text="Test" template="block.phtml" id_path="p/1"'
        ];

        $this->appStateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->with('frontend', [$this->filterEmulate, 'generateWidget'], [$construction])
            ->willReturn($result);
        $this->assertSame($result, $this->filterEmulate->widgetDirective($construction));
    }
}
