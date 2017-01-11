<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * @magentoAppArea adminhtml
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $config = $this->getMockBuilder(
            \Magento\Framework\View\Layout\PageType\Config::class
        )->setMethods(
            ['getPageTypes']
        )->disableOriginalConstructor()->getMock();
        $pageTypeValues = [
            'wishlist_index_index' => [
                'label' => 'Customer My Account My Wish List',
                'id' => 'wishlist_index_index',
            ],
            'cms_index_nocookies' => ['label' => 'CMS No-Cookies Page', 'id' => 'cms_index_nocookies'],
            'cms_index_defaultindex' => ['label' => 'CMS Home Default Page', 'id' => 'cms_index_defaultindex'],
        ];
        $config->expects($this->any())->method('getPageTypes')->will($this->returnValue($pageTypeValues));

        $this->_block = new \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Layout(
            $objectManager->get(\Magento\Framework\View\Element\Template\Context::class),
            $config,
            [
                'name' => 'page_type',
                'id' => 'page_types_select',
                'class' => 'page-types-select',
                'title' => 'Page Types Select'
            ]
        );
    }

    public function testToHtml()
    {
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_files/page_types_select.html', $this->_block->toHtml());
    }
}
