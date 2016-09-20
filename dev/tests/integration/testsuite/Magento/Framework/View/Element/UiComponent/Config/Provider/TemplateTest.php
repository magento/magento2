<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\Config\Provider;

use \Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Frontend\Pool;

/**
 * @magentoComponentsDir Magento/Framework/View/_files/UiComponent/theme
 * @magentoDbIsolation enabled
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
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
        $this->cleanCache();

        $resultOne = $this->model->getTemplate('test.xml');
        $resultTwo = $this->model->getTemplate('test.xml');

        $this->assertEquals($expected, $resultOne);
        $this->assertEquals($expected, $resultTwo);
    }

    /**
     * Clean application cache
     */
    protected function cleanCache()
    {
        /** @var Pool $cachePool */
        $cachePool = $this->objectManager->get(Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
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
}
