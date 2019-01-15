<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template\Config;

use Magento\Framework\Component\ComponentRegistrar;

class FileResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $fileIteratorFactory = $this->createMock(\Magento\Framework\Config\FileIteratorFactory::class);
        $dirSearch = $this->createMock(\Magento\Framework\Component\DirSearch::class);
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
