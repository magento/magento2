<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Block\System\Config;

use Magento\Backend\Model\Url;
use Magento\Config\Block\System\Config\Tabs;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TabsTest extends TestCase
{
    /**
     * @var Tabs
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_structureMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var MockObject
     */
    protected $_urlBuilderMock;

    protected function setUp(): void
    {
        $this->_requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getParam'
        )->with(
            'section'
        )->willReturn(
            'currentSectionId'
        );
        $this->_structureMock = $this->createMock(Structure::class);
        $this->_structureMock->expects($this->once())->method('getTabs')->willReturn([]);
        $this->_urlBuilderMock = $this->createMock(Url::class);

        $data = [
            'configStructure' => $this->_structureMock,
            'request' => $this->_requestMock,
            'urlBuilder' => $this->_urlBuilderMock,
        ];
        $helper = new ObjectManager($this);
        $this->_object = $helper->getObject(Tabs::class, $data);
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

        $sectionMock = $this->createMock(Section::class);
        $sectionMock->expects($this->once())->method('getId')->willReturn('testSectionId');

        $this->assertEquals('testSectionUrl', $this->_object->getSectionUrl($sectionMock));
    }

    public function testIsSectionActiveReturnsTrueForActiveSection()
    {
        $sectionMock = $this->createMock(Section::class);
        $sectionMock->expects($this->once())->method('getId')->willReturn('currentSectionId');
        $this->assertTrue($this->_object->isSectionActive($sectionMock));
    }

    public function testIsSectionActiveReturnsFalseForNonActiveSection()
    {
        $sectionMock = $this->createMock(Section::class);
        $sectionMock->expects($this->once())->method('getId')->willReturn('nonCurrentSectionId');
        $this->assertFalse($this->_object->isSectionActive($sectionMock));
    }
}
