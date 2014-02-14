<?php
/**
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

namespace Magento\Tools\View;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shell
     */
    protected $shell;

    /**
     * @var \Magento\Filesystem\Driver\File
     */
    protected $filesystem;

    /**
     * Temporary destination directory
     *
     * @var string
     */
    protected $tmpDir;

    protected function setUp()
    {
        $this->tmpDir = BP . '/var/static';
        $this->shell = new \Magento\Shell(new \Magento\OSInfo());
        $this->filesystem = new \Magento\Filesystem\Driver\File();
        if (!$this->filesystem->isExists($this->tmpDir)) {
            $this->filesystem->createDirectory($this->tmpDir, 0777);
        }
    }

    protected function tearDown()
    {
        if ($this->filesystem->isExists($this->tmpDir)) {
            $this->filesystem->deleteDirectory($this->tmpDir);
        }
    }

    /**
     * Test view generator
     */
    public function testViewGenerator()
    {
        try {
            $this->shell->execute(
                'php -f %s -- --source %s --destination %s',
                array(BP . '/dev/tools/Magento/Tools/View/generator.php', BP . '/app/design', $this->tmpDir)
            );
        } catch (\Magento\Exception $exception) {
            $this->fail($exception->getPrevious()->getMessage());
        }
    }
}
