<?php
/**
 * Integration test for \Magento\Framework\Code\Generator\FileResolver
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Code\Generator;

use Magento\TestFramework\Helper\Bootstrap;

class FileResolverTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Code\Generator\FileResolver
     */
    protected $model;

    /**
     * @var string original include-path variable
     */
    protected $originalPath;

    public function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create('Magento\Framework\Code\Generator\FileResolver');
        $this->originalPath = get_include_path();
        set_include_path('/pre/existing/paths/');
    }

    public function tearDown()
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
        $includePath = realpath(__DIR__ . '/../_files/');
        $className = '\ClassToFind';

        $this->model->addIncludePath($includePath);
        $this->assertFileExists($this->model->getFile($className));
    }
}
