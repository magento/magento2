<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Widget\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class WidgetTest extends \Magento\Backend\Utility\Controller
{
    /**
     * Partially covers \Magento\Widget\Block\Adminhtml\Widget\Options::_addField()
     */
    public function testLoadOptionsAction()
    {
        $this->getRequest()->setPost(
            'widget',
            '{"widget_type":"Magento\\\\Cms\\\\Block\\\\Widget\\\\Page\\\\Link","values":{}}'
        );
        $this->dispatch('backend/admin/widget/loadOptions');
        $output = $this->getResponse()->getBody();
        //searching for label with text "CMS Page"
        $this->assertContains(
            'data-ui-id="wysiwyg-widget-options-fieldset-element-label-parameters-page-id-label" >' . '<span>CMS Page',
            $output
        );
    }
}
