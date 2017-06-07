<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

/**
 * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Template
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Newsletter\Model\Template::class
        );
    }

    /**
     * This test expects next themes for areas:
     * current_store design/theme/full_name Magento/luma
     * fixturestore_store design/theme/full_name Magento/blank
     *
     * @magentoAppIsolation  enabled
     * @magentoAppArea adminhtml
     * @dataProvider getProcessedTemplateFrontendDataProvider
     */
    public function testGetProcessedTemplateFrontend($store, $design)
    {
        $this->_model->setTemplateText('{{view url="Magento_Theme::favicon.ico"}}');
        if ($store != 'default') {
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\App\Config\MutableScopeConfigInterface::class
            )->setValue(
                \Magento\Theme\Model\View\Design::XML_PATH_THEME_ID,
                $design,
                'store',
                $store
            );
        }
        $this->_model->emulateDesign($store, 'frontend');
        $processedTemplate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\State::class
        )->emulateAreaCode(
            'frontend',
            [$this->_model, 'getProcessedTemplate']
        );
        $expectedTemplateText = "frontend/{$design}/en_US/Magento_Theme/favicon.ico";
        $this->assertStringEndsWith($expectedTemplateText, $processedTemplate);
        $this->_model->revertDesign();
    }

    /**
     * @return array
     */
    public function getProcessedTemplateFrontendDataProvider()
    {
        return [
            'frontend' => ['default', 'Magento/luma'],
            'frontend store' => ['fixturestore', 'Magento/blank']
        ];
    }

    /**
     * This test expects next themes for areas:
     * adminhtml/design/theme/full_name Magento/backend
     *
     * @magentoAppIsolation  enabled
     * @dataProvider getProcessedTemplateAreaDataProvider
     */
    public function testGetProcessedTemplateArea($area, $design)
    {
        $this->_model->setTemplateText('{{view url="Magento_Theme::favicon.ico"}}');
        $this->_model->emulateDesign('default', $area);
        $processedTemplate = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\State::class
        )->emulateAreaCode(
            $area,
            [$this->_model, 'getProcessedTemplate']
        );
        $expectedTemplateText = "{$area}/{$design}/en_US/Magento_Theme/favicon.ico";
        $this->assertStringEndsWith($expectedTemplateText, $processedTemplate);
    }

    /**
     * @return array
     */
    public function getProcessedTemplateAreaDataProvider()
    {
        return [
            'backend' => ['adminhtml', 'Magento/backend']
        ];
    }

    /**
     * @magentoConfigFixture current_store system/smtp/disable 0
     * @magentoAppIsolation enabled
     * @dataProvider isValidToSendDataProvider
     */
    public function testIsValidToSend($senderEmail, $senderName, $subject, $isValid)
    {
        $this->_model->setTemplateSenderEmail(
            $senderEmail
        )->setTemplateSenderName(
            $senderName
        )->setTemplateSubject(
            $subject
        );
        $this->assertSame($isValid, $this->_model->isValidForSend());
    }

    /**
     * @return array
     */
    public function isValidToSendDataProvider()
    {
        return [
            ['john.doe@example.com', 'john.doe', 'Test Subject', true],
            ['john.doe@example.com', 'john.doe', '', false],
            ['john.doe@example.com', '', 'Test Subject', false],
            ['john.doe@example.com', '', '', false],
            ['', 'john.doe', 'Test Subject', false],
            ['', '', 'Test Subject', false],
            ['', 'john.doe', '', false],
            ['', '', '', false]
        ];
    }
}
