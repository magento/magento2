<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
 */
class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Template
     */
    protected $_model = null;

    protected function setUp(): void
    {
        $this->_model = Bootstrap::getObjectManager()->create(
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
            Bootstrap::getObjectManager()->get(
                \Magento\Framework\App\Config\MutableScopeConfigInterface::class
            )->setValue(
                \Magento\Theme\Model\View\Design::XML_PATH_THEME_ID,
                $design,
                'store',
                $store
            );
        }
        $this->_model->emulateDesign($store, 'frontend');
        $processedTemplate = Bootstrap::getObjectManager()->get(
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
        $processedTemplate = Bootstrap::getObjectManager()->get(
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

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testLegacyTemplateFromDbLoadsInStrictMode()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->_model->setTemplateType(TemplateTypesInterface::TYPE_HTML);
        $templateText = '{{var store.isSaveAllowed()}} - {{template config_path="foobar"}}';
        $this->_model->setTemplateText($templateText);
        $this->_model->setTemplateId('abc');

        $template = $objectManager->create(\Magento\Email\Model\Template::class);
        $templateData = [
            'template_code' => 'some_unique_code',
            'template_type' => TemplateTypesInterface::TYPE_HTML,
            'template_text' => '{{var this.template_code}}'
                . ' - {{var store.isSaveAllowed()}} - {{var this.getTemplateCode()}}',
        ];
        $template->setData($templateData);
        $template->save();

        // Store the ID of the newly created template in the system config so that this template will be loaded
        $objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->setValue('foobar', $template->getId(), ScopeInterface::SCOPE_STORE, 'default');

        $this->_model->emulateDesign('default', 'frontend');
        $processedTemplate = Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\State::class
        )->emulateAreaCode(
            'frontend',
            [$this->_model, 'getProcessedTemplate']
        );
        self::assertEquals(' - some_unique_code -  - some_unique_code', $processedTemplate);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testTemplateFromDbLoadsInStrictMode()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->_model->setTemplateType(TemplateTypesInterface::TYPE_HTML);
        $templateText = '{{var store.isSaveAllowed()}} - {{template config_path="foobar"}}';
        $this->_model->setTemplateText($templateText);
        $this->_model->setTemplateId('abc');

        $template = $objectManager->create(\Magento\Email\Model\Template::class);
        $templateData = [
            'template_code' => 'some_unique_code',
            'template_type' => TemplateTypesInterface::TYPE_HTML,
            'template_text' => '{{var this.template_code}}'
                . ' - {{var store.isSaveAllowed()}} - {{var this.getTemplateCode()}}',
        ];
        $template->setData($templateData);
        $template->save();

        // Store the ID of the newly created template in the system config so that this template will be loaded
        $objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->setValue('foobar', $template->getId(), ScopeInterface::SCOPE_STORE, 'default');

        $this->_model->emulateDesign('default', 'frontend');
        $processedTemplate = Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\State::class
        )->emulateAreaCode(
            'frontend',
            [$this->_model, 'getProcessedTemplate']
        );
        self::assertEquals(' - some_unique_code -  - some_unique_code', $processedTemplate);
    }
}
