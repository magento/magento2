<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Template\File;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\View\FileSystem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
            ->onlyMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
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
            ->willReturn('path_to' . $template);
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
        $this->assertEquals('path_to' . $template, $this->_resolver->getTemplateFileName($template));
    }
}
