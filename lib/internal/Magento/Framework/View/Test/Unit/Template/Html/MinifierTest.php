<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Template\Html;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Template\Html\Minifier;

class MinifierTest extends TestCase
{
    /**
     * @var Minifier
     */
    protected $object;

    /**
     * @var Filesystem|MockObject
     */
    protected $htmlDirectoryMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $appDirectoryMock;

    /**
     * @var Filesystem\Directory\ReadFactory|MockObject
     */
    protected $readFactoryMock;

    /**
     * @var ReadInterface|MockObject
     */
    protected $rootDirectoryMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->htmlDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();
        $this->appDirectoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $this->rootDirectoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readFactoryMock = $this->getMockBuilder(ReadFactory::class)
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
     * Covered method getPathToMinified.
     *
     * @return void
     * @test
     */
    public function testGetPathToMinified(): void
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
     * Covered method minify and test regular expressions.
     * @test
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMinify(): void
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
<span><?php // foo ?><?php // bar ?></span>

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
        <img src="data:image/gif;base64,P///yH5BAEAAAA" data-component="main-image"><?= \$block->someMethod(); ?>
        <div style="width: 800px" class="<?php echo \$block->getClass() ?>" />
        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-component="main-image">
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
    <?php
    \$sometext = <<<SOMETEXT
    mytext
    mytextline2
SOMETEXT;
    ?>
</html>
TEXT;

        $expectedContent = <<<TEXT
<?php /** * Copyright © Magento, Inc. All rights reserved. * See COPYING.txt for license details. */ ?> <?php ?> <span><?php ?><?php ?></span> <html><head><title>Test title</title></head><link rel="stylesheet" href='https://www.example.com/2' type="text/css" /><link rel="stylesheet" type="text/css" media="all" href="https://www.example.com/1" type="text/css" /><body><a href="http://somelink.com/text.html">Text Link</a> <img src="test.png" alt="some text" /><?php echo \$block->someMethod(); ?> <img src="data:image/gif;base64,P///yH5BAEAAAA" data-component="main-image"><?= \$block->someMethod(); ?> <div style="width: 800px" class="<?php echo \$block->getClass() ?>" /><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-component="main-image"><script>
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
</script><?php echo "http://some.link.com/" ?> <?php echo "//some.link.com/" ?> <?php echo '//some.link.com/' ?> <em>inline text</em> <a href="http://www.<?php echo 'hi' ?>"></a> <?php ?> <?php echo \$block->getChildHtml('someChildBlock'); ?> <?php ?> <?php ?> <?php ?></body><?php \$sometext = <<<SOMETEXT
    mytext
    mytextline2
SOMETEXT; ?></html>
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
     * Contain method modify and getPathToModified.
     *
     * @return void
     * @test
     */
    public function testGetMinified(): void
    {
        $file = '/absolute/path/to/phtml/template/file';
        $relativeGeneratedPath = 'absolute/path/to/phtml/template/file';

        $htmlDriver = $this->getMockForAbstractClass(DriverInterface::class);
        $htmlDriver
            ->expects($this->once())
            ->method('getRealPathSafety')
            ->willReturn($file);

        $this->htmlDirectoryMock
            ->method('isExist')
            ->withConsecutive([$relativeGeneratedPath])
            ->willReturnOnConsecutiveCalls(false);

        $this->htmlDirectoryMock
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($htmlDriver);

        $this->object->getMinified($file);
    }
}
