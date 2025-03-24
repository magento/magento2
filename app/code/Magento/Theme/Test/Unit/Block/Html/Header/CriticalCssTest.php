<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html\Header;

use Magento\Theme\Block\Html\Header\CriticalCss;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Element\Template\Context;

class CriticalCssTest extends TestCase
{

    private const BASE_CRITICAL_CSS_PATH = 'css/critical.css';
    private const CUSTOM_CRITICAL_CSS_PATH = 'css/custom-critical.css';
    private const NON_EXISTENT_CRITICAL_CSS_PATH = 'css/i-do-not-exist.css';

    private const STUBS_CRITICAL_CSS_CONTENT = [
        self::BASE_CRITICAL_CSS_PATH    => 'MOCK DEFAULT CSS CONTENT',
        self::CUSTOM_CRITICAL_CSS_PATH  => 'MOCK CUSTOM CSS CONTENT',
    ];

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    /**
     * @var File|MockObject
     */
    private $file;

    /**
     * @var Context|MockObject
     */
    private $context;

    protected function setup(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->file = $this->createMock(File::class);
        $this->assetRepo = $this->createMock(Repository::class);
        $this->assetRepo->method('createAsset')->willReturn($this->file);
    }

    public function testGetCriticalCssData()
    {
        $this->file->method('getContent')
            ->willReturn(self::STUBS_CRITICAL_CSS_CONTENT[self::BASE_CRITICAL_CSS_PATH]);

        $this->assetRepo->method('createAsset')
            ->with(self::BASE_CRITICAL_CSS_PATH);

        $block = new CriticalCss(
            $this->context,
            $this->assetRepo,
            ['file_path' => self::BASE_CRITICAL_CSS_PATH]
        );

        $this->assertEquals(
            self::STUBS_CRITICAL_CSS_CONTENT[self::BASE_CRITICAL_CSS_PATH],
            $block->getCriticalCssData()
        );
    }

    public function testGetCriticalCssDataWithCustomFilePath()
    {
        $this->file->method('getContent')
            ->willReturn(self::STUBS_CRITICAL_CSS_CONTENT[self::CUSTOM_CRITICAL_CSS_PATH]);


        $this->assetRepo->method('createAsset')
            ->with(self::CUSTOM_CRITICAL_CSS_PATH);

        $block = new CriticalCss(
            $this->context,
            $this->assetRepo,
             ['file_path' => self::CUSTOM_CRITICAL_CSS_PATH]
        );

        $this->assertEquals(
            self::STUBS_CRITICAL_CSS_CONTENT[self::CUSTOM_CRITICAL_CSS_PATH],
            $block->getCriticalCssData()
        );
    }

    public function testGetCriticalCssDataWithNonExistentFilePath()
    {
        $this->file->method('getContent')
            ->willThrowException(new NotFoundException(
                'Unable to get content for ' . self::NON_EXISTENT_CRITICAL_CSS_PATH
            ));

        $this->assetRepo->method('createAsset')
            ->with(self::NON_EXISTENT_CRITICAL_CSS_PATH);

        $block = new CriticalCss(
            $this->context,
            $this->assetRepo,
             ['file_path' => self::NON_EXISTENT_CRITICAL_CSS_PATH]
        );

        $this->assertEquals('', $block->getCriticalCssData());
    }

    public function testGetCriticalCssDataWithDefaultFilePath()
    {
        $this->file->method('getContent')
            ->willReturn('');


        $this->assetRepo->method('createAsset')
            ->with('');

        $block = new CriticalCss(
            $this->context,
            $this->assetRepo,
            []
        );

        $this->assertEquals('', $block->getCriticalCssData());
    }

}
