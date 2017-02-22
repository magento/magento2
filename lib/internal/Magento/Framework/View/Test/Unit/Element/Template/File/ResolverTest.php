<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Template\File;

/**
 * Class ResolverTest
 * @package Magento\Framework\View\Test\Unit\Element\Template\File
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Resolver object
     *
     * @var \Magento\Framework\View\Element\Template\File\Resolver
     */
    protected $_resolver;

    /**
     * Mock for view file system
     *
     * @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewFileSystemMock;

    /**
     * Test Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->_viewFileSystemMock = $this->getMock('\Magento\Framework\View\FileSystem', [], [], '', false);
        $this->_resolver = new \Magento\Framework\View\Element\Template\File\Resolver(
            $this->_viewFileSystemMock
        );
    }

    /**
     * Resolver get template file name test
     *
     * @return void
     */
    public function testGetTemplateFileName()
    {
        $template = 'template.phtml';
        $this->_viewFileSystemMock->expects($this->once())
            ->method('getTemplateFileName')
            ->with($template)
            ->will($this->returnValue('path_to' . $template));
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
    }
}
