<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template\Css;

use Magento\Email\Model\Template\Css\Processor;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var Repository|MockObject
     */
    protected $assetRepository;

    /**
     * @var FallbackContext|MockObject
     */
    protected $fallbackContext;

    protected function setUp(): void
    {
        $this->assetRepository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fallbackContext = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new Processor($this->assetRepository);
    }

    public function testProcess()
    {
        $url = 'http://magento.local/pub/static/';
        $locale = 'en_US';
        $css = '@import url("{{base_url_path}}frontend/_view/{{locale}}/css/email.css");';
        $expectedCss = '@import url("' . $url . 'frontend/_view/' . $locale . '/css/email.css");';

        $this->assetRepository->expects($this->exactly(2))
            ->method('getStaticViewFileContext')
            ->willReturn($this->fallbackContext);
        $this->fallbackContext->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($url);
        $this->fallbackContext->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->assertEquals($expectedCss, $this->processor->process($css));
    }
}
