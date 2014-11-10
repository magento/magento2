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

namespace Magento\Framework\App\Filesystem;

class DirectoryListTest extends \PHPUnit_Framework_TestCase
{
    public function testRoot()
    {
        $object = new DirectoryList('/root/dir');
        $this->assertEquals($object->getRoot(), $object->getPath(DirectoryList::ROOT));
    }

    public function testDirectoriesCustomization()
    {
        $config = [DirectoryList::APP => [DirectoryList::PATH => 'foo', DirectoryList::URL_PATH => 'bar']];
        $object = new DirectoryList('/root/dir', $config);
        $this->assertFileExists($object->getPath(DirectoryList::SYS_TMP));
        $this->assertEquals('/root/dir/foo', $object->getPath(DirectoryList::APP));
        $this->assertEquals('bar', $object->getUrlPath(DirectoryList::APP));
        $this->setExpectedException(
            '\Magento\Framework\Filesystem\FilesystemException',
            "Unknown directory type: 'unknown'"
        );
        $object->getPath('unknown');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown type: test
     */
    public function testUnknownDirectory()
    {
        new DirectoryList('/root/dir', ['test' => [DirectoryList::PATH => '/baz']]);
    }
}
