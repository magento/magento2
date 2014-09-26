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
namespace Magento\UrlRewrite\Block\Cms\Page\Edit;

/**
 * Test for \Magento\UrlRewrite\Block\Cms\Page\Edit\FormTest
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get form instance
     *
     * @param array $args
     * @return \Magento\Framework\Data\Form
     */
    protected function _getFormInstance($args = array())
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\UrlRewrite\Block\Cms\Page\Edit\Form */
        $block = $layout->createBlock(
            'Magento\UrlRewrite\Block\Cms\Page\Edit\Form',
            'block',
            array('data' => $args)
        );
        $block->setTemplate(null);
        $block->toHtml();
        return $block->getForm();
    }

    /**
     * Check _formPostInit set expected fields values
     *
     * @covers \Magento\UrlRewrite\Block\Cms\Page\Edit\Form::_formPostInit
     *
     * @dataProvider formPostInitDataProvider
     *
     * @param array $cmsPageData
     * @param string $action
     * @param string $requestPath
     * @param string $targetPath
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     * @magentoAppIsolation enabled
     */
    public function testFormPostInit($cmsPageData, $action, $requestPath, $targetPath)
    {
        $args = array();
        if ($cmsPageData) {
            $args['cms_page'] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Cms\Model\Page',
                array('data' => $cmsPageData)
            );
        }
        $form = $this->_getFormInstance($args);
        $this->assertContains($action, $form->getAction());

        $this->assertEquals($requestPath, $form->getElement('request_path')->getValue());
        $this->assertEquals($targetPath, $form->getElement('target_path')->getValue());

        $this->assertTrue($form->getElement('target_path')->getData('disabled'));
    }

    /**
     * Test entity stores
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/store.php
     */
    public function testGetEntityStores()
    {
        $args = array('cms_page' => $this->_getCmsPageWithStoresMock(array(1)));
        $form = $this->_getFormInstance($args);

        $expectedStores = array(
            array('label' => 'Main Website', 'value' => array()),
            array(
                'label' => '    Main Website Store',
                'value' => array(array('label' => '    Default Store View', 'value' => 1))
            )
        );
        $this->assertEquals($expectedStores, $form->getElement('store_id')->getValues());
    }

    /**
     * Check exception is thrown when product does not associated with stores
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/store.php
     *
     * @expectedException \Magento\Framework\App\InitException
     * @expectedExceptionMessage Chosen cms page does not associated with any website.
     */
    public function testGetEntityStoresProductStoresException()
    {
        $args = array('cms_page' => $this->_getCmsPageWithStoresMock(array()));
        $this->_getFormInstance($args);
    }

    /**
     * Data provider for testing formPostInit
     * 1) Cms page is selected
     *
     * @static
     * @return array
     */
    public static function formPostInitDataProvider()
    {
        return array(
            array(
                array('page_id' => 3, 'identifier' => 'cms-page'),
                'cms_page/3',
                'cms-page',
                'cms/page/view/page_id/3'
            )
        );
    }

    /**
     * Get CMS page model mock
     *
     * @param $stores
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Cms\Model\Page
     */
    protected function _getCmsPageWithStoresMock($stores)
    {
        $resourceMock = $this->getMockBuilder(
            'Magento\Cms\Model\Resource\Page'
        )->setMethods(
            array('lookupStoreIds')
        )->disableOriginalConstructor()->getMock();
        $resourceMock->expects($this->any())->method('lookupStoreIds')->will($this->returnValue($stores));

        $cmsPageMock = $this->getMockBuilder(
            'Magento\Cms\Model\Page'
        )->setMethods(
            array('getResource', 'getId')
        )->disableOriginalConstructor()->getMock();
        $cmsPageMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $cmsPageMock->expects($this->any())->method('getResource')->will($this->returnValue($resourceMock));

        return $cmsPageMock;
    }
}
