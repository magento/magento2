<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\TestFramework\Helper\Bootstrap;

class TranslateCachingTest extends \PHPUnit_Framework_TestCase
{
    public static function tearDownAfterClass()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Cache\Type\Translate $cache */
        $cache = $objectManager->get('Magento\Framework\App\Cache\Type\Translate');
        $cache->clean();
    }

    /**
     * @magentoDataFixture Magento/Translation/_files/db_translate.php
     */
    public function testLoadDataCaching()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Translate $model */
        $model = $objectManager->get('Magento\Framework\Translate');

        $model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND); // this is supposed to cache the fixture
        $this->assertEquals('Fixture Db Translation', __('Fixture String'));

        /** @var \Magento\Translation\Model\Resource\String $translateString */
        $translateString = $objectManager->create('Magento\Translation\Model\Resource\String');
        $translateString->saveTranslate('Fixture String', 'New Db Translation');

        $this->assertEquals('Fixture Db Translation', __('Fixture String'), 'Translation is expected to be cached');

        $model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND, true);
        $this->assertEquals('New Db Translation', __('Fixture String'), 'Forced load should not use cache');
    }
}
