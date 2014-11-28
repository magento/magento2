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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dirList;

    protected function setUp()
    {
        $this->dirList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
    }

    public function testGetFile()
    {
        $object = new Reader($this->dirList);
        $this->assertEquals(Reader::DEFAULT_FILE, $object->getFile());
        $object = new Reader($this->dirList, 'custom.php');
        $this->assertEquals('custom.php', $object->getFile());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid file name: invalid_name
     */
    public function testWrongFile()
    {
        new Reader($this->dirList, 'invalid_name');
    }

    /**
     * @param string $file
     * @param array $expected
     * @dataProvider loadDataProvider
     */
    public function testLoad($file, $expected)
    {
        $this->dirList->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(__DIR__ . '/_files');
        $object = new Reader($this->dirList, $file);
        $this->assertSame($expected, $object->load());
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            [null, ['foo', 'bar']],
            ['config.php', ['foo', 'bar']],
            ['custom.php', ['baz']],
            ['nonexistent.php', []]
        ];
    }
}
