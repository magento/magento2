<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Template\Html;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Template\Html\Minifier;

class MinifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Minifier
     */
    protected $object;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $htmlDirectoryMock;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appDirectoryMock;

    /**
     * @var Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readFactoryMock;

    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectoryMock;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * Initialize testable object
     */
    protected function setUp()
    {
        $this->htmlDirectoryMock = $this->getMockBuilder(Filesystem\Directory\WriteInterface::class)
            ->getMockForAbstractClass();
        $this->appDirectoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $this->rootDirectoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactoryMock = $this->getMockBuilder(Filesystem\Directory\ReadFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::TMP_MATERIALIZATION_DIR)
            ->willReturn($this->htmlDirectoryMock);
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT, DriverPool::FILE)
            ->willReturn($this->rootDirectoryMock);
        $this->rootDirectoryMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnCallback(function ($value) {
                return ltrim($value, '/');
            });
        $this->readFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->appDirectoryMock);

        $this->object = (new ObjectManager($this))->getObject(Minifier::class, [
            'filesystem' => $this->filesystemMock,
            'readFactory' => $this->readFactoryMock,
        ]);
    }

    /**
     * Covered method getPathToMinified
     * @test
     */
    public function testGetPathToMinified()
    {
        $file = '/absolute/path/to/phtml/template/file';
        $relativeGeneratedPath = 'absolute/path/to/phtml/template/file';
        $absolutePath = '/full/path/to/compiled/html/file';

        $this->htmlDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($relativeGeneratedPath)
            ->willReturn($absolutePath);

        $this->assertEquals($absolutePath, $this->object->getPathToMinified($file));
    }

    // @codingStandardsIgnoreStart

    /**
     * Covered method minify and test regular expressions
     * @test
     */
    public function testMinify()
    {
        $file = '/absolute/path/to/phtml/template/file';
        $relativeGeneratedPath = 'absolute/path/to/phtml/template/file';
        $baseContent = <<<TEXT
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
?>
<?php //one line comment ?>
<html>
    <head>
        <title>Test title</title>
    </head>
    <link rel="stylesheet" href='https://www.example.com/2' type="text/css" />
    <link rel="stylesheet" type="text/css" media="all" href="https://www.example.com/1" type="text/css" />
    <body>
        <a href="http://somelink.com/text.html">Text Link</a>
        <img src="test.png" alt="some text" />
        <?php echo \$block->someMethod(); ?>
        <div style="width: 800px" class="<?php echo \$block->getClass() ?>" />
        <script>
            var i = 1;// comment
            var j = 1;// <?php echo 'hi' ?>
//<?php ?> ')){
// if (<?php echo __('hi')) { ?>
// if (<?php )) {
// comment
            //<![CDATA[
            var someVar = 123;
            testFunctionCall(function () {
                return {
                    'someProperty': test,
                    'someMethod': function () {
                        alert(<?php echo \$block->getJsAlert() ?>);
                    }
                }
            });
            //]]>
        </script>
        <?php echo "http://some.link.com/" ?>
        <?php echo "//some.link.com/" ?>
        <?php echo '//some.link.com/' ?>
        <em>inline text</em>
        <a href="http://www.<?php echo 'hi' ?>"></a>
        <?php// if (\$block->getSomeVariable() > 1):?>
            <?php echo \$block->getChildHtml('someChildBlock'); ?>
        <?php //else:?>
            <?php // echo \$block->getChildHtml('anotherChildBlock'); ?>
        <?php // endif; ?>
    </body>
</html>
TEXT;

        $expectedContent = <<<TEXT
<?php /** * Copyright © Magento, Inc. All rights reserved. * See COPYING.txt for license details. */ ?> <?php ?> <html><head><title>Test title</title></head><link rel="stylesheet" href='https://www.example.com/2' type="text/css" /><link rel="stylesheet" type="text/css" media="all" href="https://www.example.com/1" type="text/css" /><body><a href="http://somelink.com/text.html">Text Link</a> <img src="test.png" alt="some text" /><?php echo \$block->someMethod(); ?> <div style="width: 800px" class="<?php echo \$block->getClass() ?>" /><script>
            var i = 1;
            var j = 1;




            //<![CDATA[
            var someVar = 123;
            testFunctionCall(function () {
                return {
                    'someProperty': test,
                    'someMethod': function () {
                        alert(<?php echo \$block->getJsAlert() ?>);
                    }
                }
            });
            //]]>
</script><?php echo "http://some.link.com/" ?> <?php echo "//some.link.com/" ?> <?php echo '//some.link.com/' ?> <em>inline text</em> <a href="http://www.<?php echo 'hi' ?>"></a> <?php ?> <?php echo \$block->getChildHtml('someChildBlock'); ?> <?php ?> <?php ?> <?php ?></body></html>
TEXT;

        $this->appDirectoryMock->expects($this->once())
            ->method('readFile')
            ->with(basename($file))
            ->willReturn($baseContent);

        $this->htmlDirectoryMock->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $this->htmlDirectoryMock->expects($this->once())
            ->method('create');
        $this->htmlDirectoryMock->expects($this->once())
            ->method('writeFile')
            ->with($relativeGeneratedPath, $expectedContent);

        $this->object->minify($file);
    }

    // @codingStandardsIgnoreEnd

    /**
     * Contain method modify and getPathToModified
     * @test
     */
    public function testGetMinified()
    {
        $file = '/absolute/path/to/phtml/template/file';
        $relativeGeneratedPath = 'absolute/path/to/phtml/template/file';

        $htmlDriver = $this->createMock(\Magento\Framework\Filesystem\DriverInterface::class);
        $htmlDriver
            ->expects($this->once())
            ->method('getRealPathSafety')
            ->willReturn($file);

        $this->htmlDirectoryMock
            ->expects($this->at(1))
            ->method('isExist')
            ->with($relativeGeneratedPath)
            ->willReturn(false);

        $this->htmlDirectoryMock
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($htmlDriver);

        $this->object->getMinified($file);
    }
}
