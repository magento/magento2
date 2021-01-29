<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Template\File;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class ResolverTest
 * @package Magento\Framework\View\Test\Unit\Element\Template\File
 */
class ResolverTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\View\FileSystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_viewFileSystemMock;

    /**
     * @var Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * Test Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_viewFileSystemMock = $this->createMock(\Magento\Framework\View\FileSystem::class);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                
                    function ($value) {
                        return json_encode($value);
                    }
                
            );
        $this->_resolver = new \Magento\Framework\View\Element\Template\File\Resolver(
            $this->_viewFileSystemMock,
            $this->serializerMock
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
            ->willReturn('path_to' . $template);
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
    }
}
