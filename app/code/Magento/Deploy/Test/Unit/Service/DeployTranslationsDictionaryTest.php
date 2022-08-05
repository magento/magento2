<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Service;

use Magento\Deploy\Service\DeployStaticFile;
use Magento\Deploy\Service\DeployTranslationsDictionary;
use Magento\Framework\App\State;

use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use PHPUnit\Framework\MockObject\MockObject as Mock;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

/**
 * Translation Dictionaries deploy service class unit tests
 */
class DeployTranslationsDictionaryTest extends TestCase
{
    /**
     * @var DeployTranslationsDictionary
     */
    private $service;

    /**
     * @var JsTranslationConfig|Mock
     */
    private $jsTranslationConfig;

    /**
     * @var DeployStaticFile|Mock
     */
    private $deployStaticFile;

    /**
     * @var State|Mock
     */
    private $state;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $dictionary = 'js-translation.json';
        $area = 'adminhtml';
        $theme = 'Magento/backend';
        $locale = 'uk_UA';

        $this->jsTranslationConfig = $this->createPartialMock(JsTranslationConfig::class, ['getDictionaryFileName']);
        $this->jsTranslationConfig
            ->expects($this->exactly(2))
            ->method('getDictionaryFileName')
            ->willReturn($dictionary);

        $this->deployStaticFile = $this->getMockBuilder(DeployStaticFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['deployFile'])
            ->getMock();
        $this->deployStaticFile->expects($this->exactly(1))->method('deployFile')
            ->willReturnCallback(
                function ($checkDictionary, $params) use ($dictionary, $area, $theme, $locale) {
                    $this->assertEquals($dictionary, $checkDictionary);
                    $this->assertEquals($dictionary, $params['fileName']);
                    $this->assertEquals($area, $params['area']);
                    $this->assertEquals($theme, $params['theme']);
                    $this->assertEquals($locale, $params['locale']);
                }
            );

        $this->state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->setMethods(['emulateAreaCode'])
            ->getMock();
        $this->state->expects($this->exactly(1))->method('emulateAreaCode')
            ->willReturnCallback(
                function ($area, $callback) {
                    $this->assertEquals('adminhtml', $area);
                    $callback();
                }
            );

        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            [],
            '',
            false
        );

        $this->service = new DeployTranslationsDictionary(
            $this->jsTranslationConfig,
            $this->deployStaticFile,
            $this->state,
            $this->logger
        );
    }

    /**
     * @see DeployTranslationsDictionary::deploy()
     */
    public function testDeploy()
    {
        $area = 'adminhtml';
        $theme = 'Magento/backend';
        $locale = 'uk_UA';
        $this->service->deploy($area, $theme, $locale);
    }
}
