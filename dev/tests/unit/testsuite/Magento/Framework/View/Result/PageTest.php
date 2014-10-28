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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Result;

use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Result Page Test
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $page;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Core\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMerge;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\Translate\InlineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\View\Page\Config\Renderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigRenderer;

    protected function setUp()
    {
        $this->layout = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMerge = $this->getMockBuilder('Magento\Core\Model\Layout\Merge')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout->expects($this->any())
            ->method('getUpdate')
            ->will($this->returnValue($this->layoutMerge));

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageConfig = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->context = $objectManagerHelper->getObject('Magento\Framework\View\Element\Template\Context', [
            'layout' => $this->layout,
            'request' => $this->request,
            'pageConfig' => $this->pageConfig
        ]);


        $this->translateInline = $this->getMock('Magento\Framework\Translate\InlineInterface');

        $this->pageConfigRenderer = $this->getMockBuilder('Magento\Framework\View\Page\Config\Renderer')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->page = $objectManagerHelper->getObject(
            'Magento\Framework\View\Result\Page',
            [
                'context' => $this->context,
                'translateInline' => $this->translateInline,
                'pageConfigRenderer' => $this->pageConfigRenderer
            ]
        );
    }

    public function testInitLayout()
    {
        $handleDefault = 'default';
        $fullActionName = 'full_action_name';
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->will($this->returnValue($fullActionName));

        $this->layoutMerge->expects($this->at(0))
            ->method('addHandle')
            ->with($handleDefault)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(1))
            ->method('addHandle')
            ->with($fullActionName)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(2))
            ->method('isLayoutDefined')
            ->willReturn(false);

        $this->assertEquals($this->page, $this->page->initLayout());
    }

    public function testInitLayoutLayoutDefined()
    {
        $handleDefault = 'default';
        $fullActionName = 'full_action_name';
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->will($this->returnValue($fullActionName));

        $this->layoutMerge->expects($this->at(0))
            ->method('addHandle')
            ->with($handleDefault)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(1))
            ->method('addHandle')
            ->with($fullActionName)
            ->willReturnSelf();
        $this->layoutMerge->expects($this->at(2))
            ->method('isLayoutDefined')
            ->willReturn(true);
        $this->layoutMerge->expects($this->at(3))
            ->method('removeHandle')
            ->with($handleDefault)
            ->willReturnSelf();

        $this->assertEquals($this->page, $this->page->initLayout());
    }

    public function testGetConfig()
    {
        $this->assertEquals($this->pageConfig, $this->page->getConfig());
    }

    public function testGetDefaultLayoutHandle()
    {
        $fullActionName = 'Full_Action_Name';
        $expectedFullActionName = 'full_action_name';

        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->will($this->returnValue($fullActionName));

        $this->assertEquals($expectedFullActionName, $this->page->getDefaultLayoutHandle());
    }

    public function testAddPageLayoutHandles()
    {
        $fullActionName = 'Full_Action_Name';
        $defaultHandle = null;
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two'
        ];
        $expected = [
            'full_action_name',
            'full_action_name_key_one_val_one',
            'full_action_name_key_two_val_two'
        ];
        $this->request->expects($this->any())
            ->method('getFullActionName')
            ->will($this->returnValue($fullActionName));

        $this->layoutMerge->expects($this->any())
            ->method('addHandle')
            ->with($expected)
            ->willReturnSelf();

        $this->assertEquals($this->layoutMerge, $this->page->addPageLayoutHandles($parameters, $defaultHandle));
    }

    public function testAddPageLayoutHandlesWithDefaultHandle()
    {
        $defaultHandle = 'default_handle';
        $parameters = [
            'key_one' => 'val_one',
            'key_two' => 'val_two'
        ];
        $expected = [
            'default_handle',
            'default_handle_key_one_val_one',
            'default_handle_key_two_val_two'
        ];
        $this->request->expects($this->never())
            ->method('getFullActionName');

        $this->layoutMerge->expects($this->any())
            ->method('addHandle')
            ->with($expected)
            ->willReturnSelf();

        $this->assertEquals($this->layoutMerge, $this->page->addPageLayoutHandles($parameters, $defaultHandle));
    }

    public function testRenderResult()
    {
        $pageLayout  = 'page_layout';
        $fullActionName = 'full_action_aame';
        $requireJs = 'require_js';
        $layoutOutput = 'layout_output';
        $headContent =  'head_content';
        $attributesHtml =  'attributes_html';
        $attributesHead =  'attributes_head';
        $attributesBody =  'attributes_body';

        $response = $this->getMock('Magento\Framework\App\ResponseInterface', ['sendResponse', 'appendBody']);
        $response->expects($this->once())
            ->method('appendBody');

        $this->request->expects($this->atLeastOnce())
            ->method('getFullActionName')
            ->with('-')
            ->willReturn($fullActionName);

        $this->pageConfig->expects($this->any())
            ->method('getPageLayout')
            ->willReturn($pageLayout);
        $this->pageConfig->expects($this->any())
            ->method('addBodyClass')
            ->withConsecutive([$fullActionName], ['page-layout-' . $pageLayout]);

        $requireJsBlock = $this->getMock('Magento\Framework\View\Element\BlockInterface');
        $requireJsBlock->expects($this->once())
            ->method('toHtml')
            ->willReturn($requireJs);

        $this->layout->expects($this->any())
            ->method('getBlock')
            ->with('require.js')
            ->willReturn($requireJsBlock);

        $this->layout->expects($this->once())
            ->method('getOutput')
            ->willReturn($layoutOutput);

        $this->translateInline->expects($this->once())
            ->method('processResponseBody')
            ->with($layoutOutput);

        $this->pageConfigRenderer->expects($this->any())
            ->method('renderElementAttributes')
            ->willReturnMap([
                [PageConfig::ELEMENT_TYPE_HTML, $attributesHtml],
                [PageConfig::ELEMENT_TYPE_HEAD, $attributesHead],
                [PageConfig::ELEMENT_TYPE_BODY, $attributesBody]
            ]);

        $this->pageConfigRenderer->expects($this->any())
            ->method('renderHeadContent')
            ->willReturn($headContent);

        $this->page->renderResult($response);
    }
}
