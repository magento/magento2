<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Varnish;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir\Reader;
use Magento\PageCache\Model\Varnish\VclTemplateLocator;
use PHPUnit\Framework\TestCase;

class VclTemplateLocatorTest extends TestCase
{
    /**
     * @var Reader
     */
    private $readerMock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var ReadFactory
     */
    private $readFactoryMock;

    /**
     * @var DirectoryList
     */
    private $directoryListMock;

    /**
     * @var VclTemplateLocator
     */
    private $vclTemplateLocator;

    public function setUp(): void
    {
        $this->readerMock = $this->createMock(Reader::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->readFactoryMock = $this->createMock(ReadFactory::class);
        $this->directoryListMock = $this->createMock(DirectoryList::class);

        $this->vclTemplateLocator = new VclTemplateLocator(
            $this->readerMock,
            $this->readFactoryMock,
            $this->scopeConfigMock,
            $this->directoryListMock
        );
    }

    public function testGetTemplate()
    {
        $inputfile = 'test.vcl';
        $version = 6;
        $read = $this->createMock(Read::class);
        $read
            ->method('readFile')
            ->with('test.vcl')
            ->willReturn('test.vcl" file can\'t be read.');
        $this->readFactoryMock->method('create')->willReturn($read);

        $template = $this->vclTemplateLocator->getTemplate($version, $inputfile);
        $this->assertStringContainsString(
            'test.vcl" file can\'t be read.',
            $template
        );
    }
}
