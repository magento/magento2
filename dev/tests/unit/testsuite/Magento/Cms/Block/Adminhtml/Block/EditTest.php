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
namespace Magento\Cms\Block\Adminhtml\Block;

/**
 * @covers \Magento\Cms\Block\Adminhtml\Block\Edit
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Block\Adminhtml\Block\Edit
     */
    protected $this;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Cms\Model\Block|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelBlockMock;

    protected function setUp()
    {
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->modelBlockMock = $this->getMockBuilder('Magento\Cms\Model\Block')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getTitle'
                ]
            )
            ->getMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->this = $objectManager->getObject(
            'Magento\Cms\Block\Adminhtml\Block\Edit',
            [
                'registry' => $this->registryMock,
                'escaper' => $this->escaperMock
            ]
        );
    }

    /**
     * @covers \Magento\Cms\Block\Adminhtml\Block\Edit::getHeaderText
     * @param integer|null $modelBlockId
     *
     * @dataProvider getHeaderTextDataProvider
     */
    public function testGetHeaderText($modelBlockId)
    {
        $title = 'some title';
        $escapedTitle = 'escaped title';

        $this->registryMock->expects($this->atLeastOnce())
            ->method('registry')
            ->with('cms_block')
            ->willReturn($this->modelBlockMock);
        $this->modelBlockMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($modelBlockId);
        $this->modelBlockMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($title);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->with($title)
            ->willReturn($escapedTitle);

        $this->assertInternalType('string', $this->this->getHeaderText());
    }

    public function getHeaderTextDataProvider()
    {
        return [
            'modelBlockId NOT EMPTY' => ['modelBlockId' => 1],
            'modelBlockId IS EMPTY' => ['modelBlockId' => null]
        ];
    }
}
