<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\RequireJs\Test\Unit;

use Magento\Framework\Code\Minifier\AdapterInterface;
use Magento\Framework\Filesystem\File\Read;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\RequireJs\Config;
use Magento\Framework\RequireJs\Config\File\Collector\Aggregated;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\RepositoryMap;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends TestCase
{
    /**
     * @var Aggregated|MockObject
     */
    private $fileSource;

    /**
     * @var DesignInterface|MockObject
     */
    private $design;

    /**
     * @var Read|MockObject
     */
    private $fileReader;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var Config
     */
    private $object;

    /**
     * @var Minification|MockObject
     */
    private $minificationMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $minifyAdapterMock;

    /**
     * @var RepositoryMap|MockObject
     */
    private $repositoryMapMock;

    protected function setUp(): void
    {
        $this->fileSource = $this->createMock(Aggregated::class);
        $this->design = $this->getMockForAbstractClass(DesignInterface::class);

        $readFactory = $this->createMock(ReadFactory::class);
        $this->fileReader = $this->createMock(Read::class);
        $readFactory->method('create')
            ->willReturn($this->fileReader);
        $repo = $this->createMock(Repository::class);
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->addMethods([
                'getConfigPath',
                'getAreaCode',
                'getThemePath',
                'getLocale'
            ])
            ->onlyMethods(
                [
                    'getPath',
                    'getBaseUrl'
                ]
            )
            ->getMockForAbstractClass();
        $repo->expects($this->once())->method('getStaticViewFileContext')->willReturn($this->context);
        $this->minificationMock = $this->getMockBuilder(Minification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->minifyAdapterMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();

        $this->repositoryMapMock = $this->getMockBuilder(RepositoryMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new Config(
            $this->fileSource,
            $this->design,
            $readFactory,
            $repo,
            $this->minifyAdapterMock,
            $this->minificationMock,
            $this->repositoryMapMock
        );
    }

    public function testGetConfig()
    {
        $this->fileReader->expects($this->any())
            ->method('readAll')
            ->willReturnCallback(
                function ($file) {
                    return $file . ' content';
                }
            );
        $fileOne = $this->createMock(File::class);
        $fileOne->expects($this->once())
            ->method('getFilename')
            ->willReturn('some/full/relative/path/file_one.js');
        $fileOne->expects($this->once())
            ->method('getName')
            ->willReturn('file_one.js');
        $fileTwo = $this->createMock(File::class);
        $fileTwo->expects($this->once())
            ->method('getFilename')
            ->willReturn('some/full/relative/path/file_two.js');
        $fileTwo->expects($this->once())
            ->method('getName')
            ->willReturn('file_two.js');
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->design->expects($this->once())
            ->method('getDesignTheme')
            ->willReturn($theme);
        $this->fileSource->expects($this->once())
            ->method('getFiles')
            ->with($theme, Config::CONFIG_FILE_NAME)
            ->willReturn([$fileOne, $fileTwo]);
        $this->minificationMock
            ->expects($this->atLeastOnce())
            ->method('isEnabled')
            ->with('js')
            ->willReturn(true);

        $expected = <<<expected
(function(require){
(function() {
file_one.js content
require.config(config);
})();
(function() {
file_two.js content
require.config(config);
})();



})(require);
expected;

        $this->minifyAdapterMock
            ->expects($this->once())
            ->method('minify')
            ->with($expected)
            ->willReturnArgument(0);

        $actual = $this->object->getConfig();
        $this->assertEquals($actual, $expected);
    }

    public function testGetMinResolverCode()
    {
        $this->minificationMock
            ->expects($this->once())
            ->method('getExcludes')
            ->with('js')
            ->willReturn(['\.min\.']);
        $this->minificationMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with('js')
            ->willReturn(true);
        $this->minifyAdapterMock
            ->expects($this->once())
            ->method('minify')
            ->willReturnArgument(0);

        $expected = <<<code
    (function () {
        var ctx = require.s.contexts._,
            origNameToUrl = ctx.nameToUrl,
            baseUrl = ctx.config.baseUrl;

        ctx.nameToUrl = function() {
            var url = origNameToUrl.apply(ctx, arguments);
            if (url.indexOf(baseUrl)===0&&!url.match(/\.min\./)) {
                url = url.replace(/(\.min)?\.js$/, '.min.js');
            }
            return url;
        };
    })();
code;
        $this->assertEquals($expected, $this->object->getMinResolverCode());
    }

    public function testGetConfigFileRelativePath()
    {
        $this->minificationMock
            ->expects($this->any())
            ->method('addMinifiedSign')
            ->willReturnArgument(0);
        $this->context->expects($this->once())->method('getConfigPath')->willReturn('path');
        $actual = $this->object->getConfigFileRelativePath();
        $this->assertSame('path/requirejs-config.js', $actual);
    }

    public function testGetMixinsFileRelativePath()
    {
        $this->minificationMock
            ->expects($this->any())
            ->method('addMinifiedSign')
            ->willReturnArgument(0);
        $this->context->expects($this->once())->method('getPath')->willReturn('path');
        $actual = $this->object->getMixinsFileRelativePath();
        $this->assertSame('path/mage/requirejs/mixins.js', $actual);
    }

    public function testGetMinResolverRelativePath()
    {
        $this->minificationMock
            ->expects($this->any())
            ->method('addMinifiedSign')
            ->willReturnArgument(0);
        $this->context->expects($this->once())->method('getConfigPath')->willReturn('path');
        $actual = $this->object->getMinResolverRelativePath();
        $this->assertSame('path/requirejs-min-resolver.js', $actual);
    }

    public function testGetBaseConfig()
    {
        $this->context->expects($this->once())->method('getPath')->willReturn('area/theme/locale');
        $this->context->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('http://base.url/');
        $expected = <<<expected
require.config({"baseUrl":"http://base.url/area/theme/locale"});
expected;
        $actual = $this->object->getBaseConfig();
        $this->assertSame($expected, $actual);
    }
}
