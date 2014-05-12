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
            'Magento\Framework\View\Layout\PageType\Config'
        )->setMethods(
            array('getPageTypes')
        )->disableOriginalConstructor()->getMock();
        $pageTypeValues = array(
            'wishlist_index_index' => array(
                'label' => 'Customer My Account My Wish List',
                'id' => 'wishlist_index_index'
            ),
            'cms_index_nocookies' => array('label' => 'CMS No-Cookies Page', 'id' => 'cms_index_nocookies'),
            'cms_index_defaultindex' => array('label' => 'CMS Home Default Page', 'id' => 'cms_index_defaultindex')
        );
        $config->expects($this->any())->method('getPageTypes')->will($this->returnValue($pageTypeValues));

        $this->_block = new \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Layout(
            $objectManager->get('Magento\Framework\View\Element\Template\Context'),
            $config,
            array(
                'name' => 'page_type',
                'id' => 'page_types_select',
                'class' => 'page-types-select',
                'title' => 'Page Types Select'
            )
        );
    }

    public function testToHtml()
    {
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/_files/page_types_select.html', $this->_block->toHtml());
    }
}
