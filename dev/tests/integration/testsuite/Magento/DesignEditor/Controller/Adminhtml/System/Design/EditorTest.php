<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design;

/**
 * @magentoAppArea adminhtml
 */
class EditorTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_dataHelper;

    protected function setUp()
    {
        parent::setUp();
        $this->_dataHelper = $this->_objectManager->get('Magento\Core\Helper\Data');
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/admin/system_design_editor/index');
        $content = $this->getResponse()->getBody();

        $this->assertContains('<div class="infinite_scroll">', $content);
        $this->assertContains("jQuery('.infinite_scroll').infinite_scroll", $content);
    }

    public function testLaunchActionSingleStoreWrongThemeId()
    {
        $wrongThemeId = 999;
        $this->getRequest()->setParam('theme_id', $wrongThemeId);
        $this->dispatch('backend/admin/system_design_editor/launch');
        $this->assertSessionMessages(
            $this->equalTo(['We can\'t find theme "' . $wrongThemeId . '".']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $expected = 'http://localhost/index.php/backend/admin/system_design_editor/index/';
        $this->assertRedirect($this->stringStartsWith($expected));
    }
}
