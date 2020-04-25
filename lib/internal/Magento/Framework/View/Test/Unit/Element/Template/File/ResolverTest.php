<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Template\File;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\FileSystem;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\Serialize\Serializer\Json;

class ResolverTest extends TestCase
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
     * @var FileSystem|MockObject
     */
    protected $_viewFileSystemMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * Test Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_viewFileSystemMock = $this->createMock(FileSystem::class);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );
        $this->_resolver = new Resolver(
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
            ->will($this->returnValue('path_to' . $template));
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
    }
}
