<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template\Config;

use Magento\Framework\Component\ComponentRegistrar;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $fileIteratorFactory = $this->getMock('\Magento\Framework\Config\FileIteratorFactory', [], [], '', false);
        $dirSearch = $this->getMock('\Magento\Framework\Component\DirSearch', [], [], '', false);
        $model = new \Magento\Email\Model\Template\Config\FileResolver($fileIteratorFactory, $dirSearch);
        $expected = ['found_file'];
        $fileIteratorFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expected));
        $dirSearch->expects($this->once())
            ->method('collectFiles')
            ->with(ComponentRegistrar::MODULE, 'etc/file');
        $model->get('file', 'scope');
    }
}
