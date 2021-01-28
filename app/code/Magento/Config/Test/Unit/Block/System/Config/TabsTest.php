<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Block\System\Config;

class TabsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Block\System\Config\Tabs
     */
    protected $_object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_structureMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlBuilderMock;

    protected function setUp(): void
    {
        $this->_requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'section'
        )->willReturn(
            'currentSectionId'
        );
        $this->_structureMock = $this->createMock(\Magento\Config\Model\Config\Structure::class);
        $this->_structureMock->expects($this->once())->method('getTabs')->willReturn([]);
        $this->_urlBuilderMock = $this->createMock(\Magento\Backend\Model\Url::class);

        $data = [
            'configStructure' => $this->_structureMock,
            'request' => $this->_requestMock,
            'urlBuilder' => $this->_urlBuilderMock,
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $helper->getObject(\Magento\Config\Block\System\Config\Tabs::class, $data);
    }

    protected function tearDown(): void
    {
        unset($this->_object);
        unset($this->_requestMock);
        unset($this->_structureMock);
        unset($this->_urlBuilderMock);
    }

    public function testGetSectionUrl()
    {
        $this->_urlBuilderMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/*/*',
            ['_current' => true, 'section' => 'testSectionId']
        )->willReturn(
            'testSectionUrl'
        );

        $sectionMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Section::class);
        $sectionMock->expects($this->once())->method('getId')->willReturn('testSectionId');

        $this->assertEquals('testSectionUrl', $this->_object->getSectionUrl($sectionMock));
    }

    public function testIsSectionActiveReturnsTrueForActiveSection()
    {
        $sectionMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Section::class);
        $sectionMock->expects($this->once())->method('getId')->willReturn('currentSectionId');
        $this->assertTrue($this->_object->isSectionActive($sectionMock));
    }

    public function testIsSectionActiveReturnsFalseForNonActiveSection()
    {
        $sectionMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Section::class);
        $sectionMock->expects($this->once())->method('getId')->willReturn('nonCurrentSectionId');
        $this->assertFalse($this->_object->isSectionActive($sectionMock));
    }
}
