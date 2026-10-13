<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Service\MinifyTemplates;
use Magento\Framework\App\Utility\Files;

use Magento\Framework\View\Template\Html\MinifierInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;

use PHPUnit\Framework\TestCase;

/**
 * Minify Templates service class unit tests
 */
class MinifyTemplatesTest extends TestCase
{
    /**
     * @var MinifyTemplates
     */
    private $service;

    /**
     * @var Files|Mock
     */
    private $filesUtils;

    /**
     * @var MinifierInterface|Mock
     */
    private $htmlMinifier;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->filesUtils = $this->createPartialMock(Files::class, ['getPhtmlFiles']);

        $this->htmlMinifier = $this->createMock(MinifierInterface::class);

        $this->service = new MinifyTemplates(
            $this->filesUtils,
            $this->htmlMinifier
        );
    }

    /**
     * @see MinifyTemplates::minifyTemplates()
     */
    public function testMinifyTemplates()
    {
        $templateMock = "template.phtml";
        $templatesMock = [$templateMock];

        $this->filesUtils->expects($this->once())
            ->method('getPhtmlFiles')
            ->with(false, false)
            ->willReturn($templatesMock);

        $this->htmlMinifier->expects($this->once())->method('minify')->with($templateMock);

        $this->assertEquals(1, $this->service->minifyTemplates());
    }
}
