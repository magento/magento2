<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Sales\Model\Email\Template
 */
namespace Magento\Sales\Model\Email;

use Magento\Email\Model\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Email\Template
     */
    private $template;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\View\FileSystem
     */
    private $mockViewFilesystem;

    public function setUp()
    {
        $this->mockViewFilesystem = $this->getMockBuilder('\Magento\Framework\View\FileSystem')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Email\Model\Resource\Template')
            ->will($this->returnValue($objectManagerHelper->getObject('Magento\Email\Model\Resource\Template')));
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->template = $objectManagerHelper->getObject(
            'Magento\Sales\Model\Email\Template',
            [
                'viewFileSystem' => $this->mockViewFilesystem,
            ]
        );
    }

    public function testIncludeTemplate()
    {
        $this->mockViewFilesystem->expects($this->once())
            ->method('getTemplateFileName')
            ->with('template')
            ->will($this->returnValue(__DIR__ . '/_files/test_include.php'));
        $include = $this->template->getInclude('template', ['one' => 1, 'two' => 2]);
        $this->assertEquals('Number One = 1. Number Two = 2', $include);
    }

    public function testNoFilename()
    {
        $this->mockViewFilesystem->expects($this->once())
            ->method('getTemplateFileName')
            ->with('template')
            ->will($this->returnValue(false));
        $include = $this->template->getInclude('template', ['one' => 1, 'two' => 2]);
        $this->assertEquals('', $include);
    }
}
