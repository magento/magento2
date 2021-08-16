<?php
/**
 * @see       https://github.com/laminas/laminas-captcha for the canonical source repository
 * @copyright https://github.com/laminas/laminas-captcha/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-captcha/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Captcha\Test\Unit\Model\Laminas\Captcha;

use DirectoryIterator;
use Magento\Captcha\Model\Laminas\Captcha\Exception;
use Magento\Captcha\Model\Laminas\Captcha\Image;
use Magento\Captcha\Test\Unit\Model\Laminas\Captcha\TestAsset\SessionContainer;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Captcha
 */
class ImageTest extends TestCase
{
    /** @var string */
    protected $tmpDir;

    /** @var string */
    protected $testDir;

    /** @var Image */
    protected $captcha;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('The GD extension is not available.');
        }
        if (! function_exists("imagepng")) {
            $this->markTestSkipped("Image CAPTCHA requires PNG support");
        }
        if (! function_exists("imageftbbox")) {
            $this->markTestSkipped("Image CAPTCHA requires FT fonts support");
        }

        if (isset($this->word)) {
            unset($this->word);
        }

        $this->testDir = $this->getTmpDir() . '/Laminas_test_images';

        if (!is_dir($this->testDir)) {
            @mkdir($this->testDir);
        }

        $this->captcha = new Image([
            'sessionClass' => SessionContainer::class,
            'imgDir'       => $this->testDir,
            'font'         => __DIR__ . '/_files/Vera.ttf',
        ]);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown(): void
    {
        // remove captcha images
        foreach (new DirectoryIterator($this->testDir) as $file) {
            if (!$file->isDot() && ! $file->isDir()) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * Determine system TMP directory
     *
     * @return string
     * @throws Exception\RuntimeException If unable to determine directory.
     */
    protected function getTmpDir()
    {
        if (null === $this->tmpDir) {
            $this->tmpDir = sys_get_temp_dir();
        }
        return $this->tmpDir;
    }

    public function testCaptchaSetSuffix(): void
    {
        $this->captcha->setSuffix(".jpeg");
        $this->assertEquals('.jpeg', $this->captcha->getSuffix());
    }

    public function testCaptchaSetImgURL(): void
    {
        $this->captcha->setImgUrl("/some/other/url/");
        $this->assertEquals('/some/other/url/', $this->captcha->getImgUrl());
    }

    public function testCaptchaCreatesImage(): void
    {
        $this->captcha->generate();
        $this->assertFileExists($this->testDir . "/" . $this->captcha->getId() . '.png');
    }

    public function testCaptchaSetExpiration(): void
    {
        $this->assertEquals($this->captcha->getExpiration(), 600);
        $this->captcha->setExpiration(3600);
        $this->assertEquals($this->captcha->getExpiration(), 3600);
    }

    public function testCaptchaImageCleanup(): void
    {
        $this->captcha->generate();
        $filename = $this->testDir . "/" . $this->captcha->getId() . ".png";
        $this->assertFileExists($filename);
        $this->captcha->setExpiration(1);
        $this->captcha->setGcFreq(1);
        sleep(2);
        $this->captcha->generate();
        clearstatcache();
        $this->assertFileDoesNotExist($filename, "File $filename was found even after GC");
    }

    /**
     * @group Laminas-10006
     */
    public function testCaptchaImageCleanupOnlyCaptchaFilesIdentifiedByTheirSuffix(): void
    {
        if (!getenv('TESTS_LAMINAS_CAPTCHA_GC')) {
            $this->markTestSkipped('Enable TESTS_LAMINAS_CAPTCHA_GC to run this test');
        }
        $this->captcha->generate();
        $filename = $this->testDir . "/" . $this->captcha->getId() . ".png";
        $this->assertFileExists($filename);

        //Create other cache file
        $otherFile = $this->testDir . "/laminas10006.cache";
        file_put_contents($otherFile, '');
        $this->assertFileExists($otherFile);
        $this->captcha->setExpiration(1);
        $this->captcha->setGcFreq(1);
        sleep(2);
        $this->captcha->generate();
        clearstatcache();
        $this->assertFileDoesNotExist($filename, "File $filename was found even after GC");
        $this->assertFileExists($otherFile, "File $otherFile was not found after GC");
    }

    public function testGenerateReturnsId(): void
    {
        $id = $this->captcha->generate();
        $this->assertNotEmpty($id);
        $this->assertIsString($id);
        $this->id = $id;
    }

    public function testGetWordReturnsWord(): void
    {
        $this->captcha->generate();
        $word = $this->captcha->getWord();
        $this->assertNotEmpty($word);
        $this->assertIsString($word);
        $this->assertEquals(8, strlen($word));
        $this->word = $word;
    }

    public function testGetWordLength(): void
    {
        $this->captcha->setWordLen(4);
        $this->captcha->generate();
        $word = $this->captcha->getWord();
        $this->assertIsString($word);
        $this->assertEquals(4, strlen($word));
        $this->word = $word;
    }

    public function testGenerateIsRandomised(): void
    {
        $id1   = $this->captcha->generate();
        $word1 = $this->captcha->getWord();
        $id2   = $this->captcha->generate();
        $word2 = $this->captcha->getWord();

        $this->assertNotEmpty($id1);
        $this->assertNotEmpty($id2);
        $this->assertNotEquals($id1, $id2);
        $this->assertNotEquals($word1, $word2);
    }

    public function testRenderInitializesSessionData(): void
    {
        $this->captcha->generate();
        $session = $this->captcha->getSession();
        $this->assertEquals($this->captcha->getTimeout(), $session->setExpirationSeconds);
        $this->assertEquals(1, $session->setExpirationHops);
        $this->assertEquals($this->captcha->getWord(), $session->word);
    }

    public function testWordValidates(): void
    {
        $this->captcha->generate();
        $input = ["id" => $this->captcha->getId(), "input" => $this->captcha->getWord()];
        $this->assertTrue($this->captcha->isValid($input));
    }

    public function testMissingNotValid(): void
    {
        $this->captcha->generate();
        $this->assertFalse($this->captcha->isValid([]));
        $input = ["input" => "blah"];
        $this->assertFalse($this->captcha->isValid($input));
    }

    public function testWrongWordNotValid(): void
    {
        $this->captcha->generate();
        $input = ["id" => $this->captcha->getId(), "input" => "blah"];
        $this->assertFalse($this->captcha->isValid($input));
    }

    public function testNoFontProvidedWillThrowException(): void
    {
        $this->expectException(Exception\NoFontProvidedException::class);
        $captcha = new Image();
        $captcha->generate();
    }

    public function testImageProvidedNotLoadableWillThrowException(): void
    {
        $this->expectException(Exception\ImageNotLoadableException::class);
        $captcha = new Image([
            'font'       => __DIR__ . '/../Pdf/_fonts/Vera.ttf',
            'startImage' => 'file_not_found.png',
        ]);
        $captcha->generate();
    }
}
