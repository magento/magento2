<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

class InlineEditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\InlineEdit
     */
    protected $inlineEditController;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->pageMock = $this->getMockBuilder('Magento\Cms\Model\Page')
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData'])
            ->getMock();

        $this->inlineEditController = $this->objectManager->getObject(
            'Magento\Cms\Controller\Adminhtml\Page\InlineEdit'
        );
    }

    public function testSetCmsPageData()
    {
        $extendedPageData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'content_heading' => 'Home Page',
            'content' => 'CMS homepage content goes here.',
            'is_active' => '1',
            'sort_order' => '1',
            'custom_theme' => '3',
            'website_root' => '1',
            'under_version_control' => '0',
            'store_id' => ['0']
        ];
        $pageData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'is_active' => '1',
            'custom_theme' => '3',
            'under_version_control' => '0',
        ];
        $getData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'content_heading' => 'Home Page',
            'content' => 'CMS homepage content goes here.',
            'is_active' => '1',
            'sort_order' => '1',
            'custom_theme' => '3',
            'custom_root_template' => '1column',
            'published_revision_id' => '0',
            'website_root' => '1',
            'under_version_control' => '0',
            'store_id' => ['0']
        ];
        $mergedData = [
            'page_id' => '2',
            'title' => 'Home Page',
            'page_layout' => '1column',
            'identifier' => 'home',
            'content_heading' => 'Home Page',
            'content' => 'CMS homepage content goes here.',
            'is_active' => '1',
            'sort_order' => '1',
            'custom_theme' => '3',
            'custom_root_template' => '1column',
            'published_revision_id' => '0',
            'website_root' => '1',
            'under_version_control' => '0',
            'store_id' => ['0']
        ];
        $this->pageMock->expects($this->once())->method('getData')->willReturn($getData);
        $this->pageMock->expects($this->once())->method('setData')->with($mergedData);

        $this->inlineEditController->setCmsPageData($this->pageMock, $extendedPageData, $pageData);
    }
}
