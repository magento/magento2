<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\Config\Provider;

use \Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * @magentoComponentsDir Magento/Framework/View/_files/UiComponent/theme
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class TemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\Config\Provider\Template
     */
    private $model;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registerThemes();
        $this->objectManager->addSharedInstance(
            $this->objectManager->create(
                \Magento\Framework\App\Arguments\ValidationState::class,
                ['appMode' => 'default']
            ),
            \Magento\Framework\App\Arguments\ValidationState::class
        );
        $this->model = $this->objectManager->create(
            \Magento\Framework\View\Element\UiComponent\Config\Provider\Template::class
        );
    }

    public function testGetTemplate()
    {
        $expected = file_get_contents(__DIR__ . '/../../../../_files/UiComponent/expected/config.xml');

        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
        $this->objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDesignTheme('FrameworkViewUiComponent/default');
        CacheCleaner::cleanAll();

        $resultOne = $this->model->getTemplate('test.xml');
        $resultTwo = $this->model->getTemplate('test.xml');

        $this->assertXmlStringEqualsXmlString($expected, $resultOne);
        $this->assertXmlStringEqualsXmlString($expected, $resultTwo);
    }

    /**
     * Register themes in the fixture folder
     */
    protected function registerThemes()
    {
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(
            \Magento\Theme\Model\Theme\Registration::class
        );
        $registration->register();
    }

    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(
            \Magento\Framework\App\Arguments\ValidationState::class
        );
    }
}
