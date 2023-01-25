<?php
/**
 * Integration test for \Magento\Framework\Filesystem\FileResolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem;

use Magento\TestFramework\Helper\Bootstrap;

class FileResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Path to add to include path
     */
    const FIRST_PATH = '/path/to/code/1/';

    /**
     * Path to add to include path
     */
    const SECOND_PATH = '/path/to/code/2/';

    /**
     * @var \Magento\Framework\Filesystem\FileResolver
     */
    protected $model;

    /**
     * @var string original include-path variable
     */
    protected $originalPath;

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->create(\Magento\Framework\Filesystem\FileResolver::class);
        $this->originalPath = get_include_path();
        set_include_path('/pre/existing/paths/');
    }

    protected function tearDown(): void
    {
        set_include_path($this->originalPath);
    }

    public function testAddIncludePathPrepend()
    {
        $this->model->addIncludePath(self::FIRST_PATH);
        $this->model->addIncludePath(self::SECOND_PATH);

        $postIncludePath = get_include_path();
        $this->assertStringStartsWith(
            self::SECOND_PATH,
            $postIncludePath
        );
    }

    public function testAddIncludePathAppend()
    {
        $this->model->addIncludePath(self::FIRST_PATH, false);
        $this->model->addIncludePath(self::SECOND_PATH, false);

        $postIncludePath = get_include_path();
        $this->assertStringEndsWith(
            self::SECOND_PATH,
            $postIncludePath
        );
    }

    public function testGetFile()
    {
        $includePath = realpath(__DIR__ . '/_files/');
        $className = '\ClassToFind';

        $this->model->addIncludePath($includePath);
        $this->assertFileExists($this->model->getFile($className));
    }
}
