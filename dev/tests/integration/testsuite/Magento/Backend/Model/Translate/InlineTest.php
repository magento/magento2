<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Translate;

/**
 * @magentoAppArea adminhtml
 */
class InlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $_translateInline;

    protected function setUp()
    {
        $this->_translateInline = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Translate\InlineInterface'
        );
    }

    /**
     * @magentoAdminConfigFixture dev/translate_inline/active_admin 1
     * @covers \Magento\Framework\Translate\Inline::getAjaxUrl
     */
    public function testAjaxUrl()
    {
        $body = '<html><body>some body</body></html>';
        /** @var \Magento\Backend\Model\UrlInterface $url */
        $url = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\UrlInterface');
        $url->getUrl(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE . '/ajax/translate');
        $this->_translateInline->processResponseBody($body, true);
        $this->assertContains(
            $url->getUrl(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE . '/ajax/translate'),
            $body
        );
    }
}
