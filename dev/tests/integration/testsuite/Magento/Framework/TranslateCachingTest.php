<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class TranslateCachingTest
 * @package Magento\Framework
 * @magentoAppIsolation enabled
 */
class TranslateCachingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Phrase\RendererInterface
     */
    protected $renderer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->renderer = Phrase::getRenderer();
        Phrase::setRenderer($this->objectManager->get(\Magento\Framework\Phrase\RendererInterface::class));
    }

    protected function tearDown()
    {
        Phrase::setRenderer($this->renderer);

        /** @var \Magento\Framework\App\Cache\Type\Translate $cache */
        $cache = $this->objectManager->get(\Magento\Framework\App\Cache\Type\Translate::class);
        $cache->clean();
    }

    /**
     * @magentoDataFixture Magento/Translation/_files/db_translate.php
     */
    public function testLoadDataCaching()
    {
        /** @var \Magento\Framework\Translate $model */
        $model = $this->objectManager->get(\Magento\Framework\Translate::class);

        $model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND); // this is supposed to cache the fixture
        $this->assertEquals('Fixture Db Translation', new Phrase('Fixture String'));

        /** @var \Magento\Translation\Model\ResourceModel\StringUtils $translateString */
        $translateString = $this->objectManager->create(\Magento\Translation\Model\ResourceModel\StringUtils::class);
        $translateString->saveTranslate('Fixture String', 'New Db Translation');

        $this->assertEquals(
            'Fixture Db Translation',
            new Phrase('Fixture String'),
            'Translation is expected to be cached'
        );

        $model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND, true);
        $this->assertEquals(
            'New Db Translation',
            new Phrase('Fixture String'),
            'Forced load should not use cache'
        );
    }
}
