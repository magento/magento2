<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\RequireJs;

use Magento\Framework\App\Filesystem\DirectoryList;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\RequireJs\Config\File\Collector\Aggregated|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $design;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $baseDir;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var Config
     */
    private $object;

    protected function setUp()
    {
        $this->fileSource = $this->getMock(
            '\Magento\Framework\RequireJs\Config\File\Collector\Aggregated',
            [],
            [],
            '',
            false
        );
        $this->design = $this->getMockForAbstractClass('\Magento\Framework\View\DesignInterface');
        $this->baseDir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue($this->baseDir));
        $repo = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->context = $this->getMockBuilder('Magento\Framework\View\Asset\ContextInterface')
            ->setMethods(
                [
                    'getConfigPath',
                    'getPath',
                    'getBaseUrl'
                ]
            )
            ->getMock();
        $repo->expects($this->once())->method('getStaticViewFileContext')->will($this->returnValue($this->context));
        $this->object = new \Magento\Framework\RequireJs\Config($this->fileSource, $this->design, $filesystem, $repo);
    }

    public function testGetConfig()
    {
        $this->baseDir->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnCallback(function ($path) {
                return 'relative/' . $path;
            }));
        $this->baseDir->expects($this->any())
            ->method('readFile')
            ->will($this->returnCallback(function ($file) {
                return $file . ' content';
            }));
        $fileOne = $this->getMock('\Magento\Framework\View\File', [], [], '', false);
        $fileOne->expects($this->once())
            ->method('getFilename')
            ->will($this->returnValue('file_one.js'));
        $fileOne->expects($this->once())
            ->method('getModule')
            ->will($this->returnValue('Module_One'));
        $fileTwo = $this->getMock('\Magento\Framework\View\File', [], [], '', false);
        $fileTwo->expects($this->once())
            ->method('getFilename')
            ->will($this->returnValue('file_two.js'));
        $theme = $this->getMockForAbstractClass('\Magento\Framework\View\Design\ThemeInterface');
        $this->design->expects($this->once())
            ->method('getDesignTheme')
            ->will($this->returnValue($theme));
        $this->fileSource->expects($this->once())
            ->method('getFiles')
            ->with($theme, Config::CONFIG_FILE_NAME)
            ->will($this->returnValue([$fileOne, $fileTwo]));

        $expected = <<<expected
(function(require){
require.config({"baseUrl":""});
(function() {
relative/file_one.js content
require.config(config);
})();
(function() {
relative/file_two.js content
require.config(config);
})();



})(require);
expected;

        $actual = $this->object->getConfig();
        $this->assertStringMatchesFormat($expected, $actual);
    }

    public function testGetConfigFileRelativePath()
    {
        $this->context->expects($this->once())->method('getConfigPath')->will($this->returnValue('path'));
        $actual = $this->object->getConfigFileRelativePath();
        $this->assertSame('_requirejs/path/requirejs-config.js', $actual);
    }

    public function testGetBaseConfig()
    {
        $this->context->expects($this->once())->method('getPath')->will($this->returnValue('area/theme/locale'));
        $this->context->expects($this->once())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://base.url/'));
        $expected = <<<expected
require.config({"baseUrl":"http://base.url/area/theme/locale"});
expected;
        $actual = $this->object->getBaseConfig();
        $this->assertSame($expected, $actual);
    }
}
