<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    protected function _getFormInstance($args = [])
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\UrlRewrite\Block\Cms\Page\Edit\Form */
        $block = $layout->createBlock(
            'Magento\UrlRewrite\Block\Cms\Page\Edit\Form',
            'block',
            ['data' => $args]
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
        $args = [];
        if ($cmsPageData) {
            $args['cms_page'] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Cms\Model\Page',
                ['data' => $cmsPageData]
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
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetEntityStores()
    {
        $args = ['cms_page' => $this->_getCmsPageWithStoresMock([1])];
        $form = $this->_getFormInstance($args);

        $expectedStores = [
            ['label' => 'Main Website', 'value' => []],
            [
                'label' => '    Main Website Store',
                'value' => [['label' => '    Default Store View', 'value' => 1]]
            ],
        ];
        $this->assertEquals($expectedStores, $form->getElement('store_id')->getValues());
    }

    /**
     * Check exception is thrown when product does not associated with stores
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetEntityStoresProductStoresException()
    {
        $args = ['cms_page' => $this->_getCmsPageWithStoresMock([])];
        $form = $this->_getFormInstance($args);
        $this->assertEquals([], $form->getElement('store_id')->getValues());
        $this->assertEquals(
            'Please assign a website to the selected CMS page.',
            $form->getElement('store_id')->getAfterElementHtml()
        );
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
        return [
            [
                ['page_id' => 3, 'identifier' => 'cms-page'],
                'cms_page/3',
                'cms-page',
                'cms/page/view/page_id/3',
            ]
        ];
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
            'Magento\Cms\Model\ResourceModel\Page'
        )->setMethods(
            ['lookupStoreIds']
        )->disableOriginalConstructor()->getMock();
        $resourceMock->expects($this->any())->method('lookupStoreIds')->will($this->returnValue($stores));

        $cmsPageMock = $this->getMockBuilder(
            'Magento\Cms\Model\Page'
        )->setMethods(
            ['getResource', 'getId']
        )->disableOriginalConstructor()->getMock();
        $cmsPageMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $cmsPageMock->expects($this->any())->method('getResource')->will($this->returnValue($resourceMock));

        return $cmsPageMock;
    }
}
