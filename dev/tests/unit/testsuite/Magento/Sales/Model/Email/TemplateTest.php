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

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager', ['create', 'configure', 'get']);
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
