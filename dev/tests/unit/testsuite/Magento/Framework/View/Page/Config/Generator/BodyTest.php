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

namespace Magento\Framework\View\Page\Config\Generator;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for page config generator model
 */
class BodyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Body
     */
    protected $bodyGenerator;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    protected function setUp()
    {
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->bodyGenerator = $objectManagerHelper->getObject(
            'Magento\Framework\View\Page\Config\Generator\Body',
            [
                'pageConfig' => $this->pageConfigMock,
            ]
        );
    }

    public function testProcess()
    {
        $generatorContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Generator\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $structureMock = $this->getMockBuilder('Magento\Framework\View\Page\Config\Structure')
            ->disableOriginalConstructor()
            ->getMock();

        $readerContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $readerContextMock->expects($this->any())
            ->method('getPageConfigStructure')
            ->willReturn($structureMock);

        $bodyClasses = ['class_1', 'class_2'];
        $structureMock->expects($this->once())
            ->method('getBodyClasses')
            ->will($this->returnValue($bodyClasses));
        $this->pageConfigMock->expects($this->exactly(2))
            ->method('addBodyClass')
            ->withConsecutive(['class_1'], ['class_2']);

        $this->assertEquals(
            $this->bodyGenerator,
            $this->bodyGenerator->process($readerContextMock, $generatorContextMock)
        );
    }
}
