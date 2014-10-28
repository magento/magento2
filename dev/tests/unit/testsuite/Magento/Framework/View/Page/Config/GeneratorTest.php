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

namespace Magento\Framework\View\Page\Config;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Test for page config generator model
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var \Magento\Framework\View\Page\Config\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structureMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    protected function setUp()
    {
        $this->structureMock = $this->getMockBuilder('Magento\Framework\View\Page\Config\Structure')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->generator = $objectManagerHelper->getObject(
            'Magento\Framework\View\Page\Config\Generator',
            [
                'structure' => $this->structureMock,
                'pageConfig' => $this->pageConfigMock,
            ]
        );
    }

    public function testProcess()
    {
        $this->structureMock->expects($this->once())->method('processRemoveAssets');
        $this->structureMock->expects($this->once())->method('processRemoveElementAttributes');

        $assets = [
            'remoteName' => ['src' => 'file-url', 'src_type' => 'url', 'media'=> "all"],
            'name' => ['src' => 'file-path', 'ie_condition' => 'lt IE 7', 'media'=> "print"]
        ];
        $this->pageConfigMock->expects($this->once())
            ->method('addRemotePageAsset')
            ->with('remoteName', Generator::VIRTUAL_CONTENT_TYPE_LINK, ['attributes' => ['media'=> 'all']]);
        $this->pageConfigMock->expects($this->once())
            ->method('addPageAsset')
            ->with('name', ['attributes' => ['media'=> 'print'], 'ie_condition' => 'lt IE 7']);
        $this->structureMock->expects($this->once())
            ->method('getAssets')
            ->will($this->returnValue($assets));

        $title = 'Page title';
        $this->structureMock->expects($this->once())
            ->method('getTitle')
            ->will($this->returnValue($title));
        $this->pageConfigMock->expects($this->once())
            ->method('setTitle')
            ->with($title);

        $metadata = ['name1' => 'content1', 'name2' => 'content2'];
        $this->structureMock->expects($this->once())
            ->method('getMetadata')
            ->will($this->returnValue($metadata));
        $this->pageConfigMock->expects($this->exactly(2))
            ->method('setMetadata')
            ->withConsecutive(['name1', 'content1'], ['name2', 'content2']);

        $elementAttributes = [
            PageConfig::ELEMENT_TYPE_BODY => [
                'body_attr_1' => 'body_value_1',
                'body_attr_2' => 'body_value_2',
            ],
            PageConfig::ELEMENT_TYPE_HTML => [
                'html_attr_1' => 'html_attr_1',
            ]
        ];
        $this->structureMock->expects($this->once())
            ->method('getElementAttributes')
            ->will($this->returnValue($elementAttributes));
        $this->pageConfigMock->expects($this->exactly(3))
            ->method('setElementAttribute')
            ->withConsecutive(
                [PageConfig::ELEMENT_TYPE_BODY, 'body_attr_1', 'body_value_1'],
                [PageConfig::ELEMENT_TYPE_BODY, 'body_attr_2', 'body_value_2'],
                [PageConfig::ELEMENT_TYPE_HTML, 'html_attr_1', 'html_attr_1']
            );

        $bodyClasses = ['class_1', 'class_2'];
        $this->structureMock->expects($this->once())
            ->method('getBodyClasses')
            ->will($this->returnValue($bodyClasses));
        $this->pageConfigMock->expects($this->exactly(2))
            ->method('addBodyClass')
            ->withConsecutive(['class_1'], ['class_2']);

        $this->assertEquals($this->generator, $this->generator->process());
    }
}
