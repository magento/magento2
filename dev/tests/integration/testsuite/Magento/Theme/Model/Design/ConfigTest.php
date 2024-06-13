<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Theme\Model\Design;

/**
 * Test for \Magento\Theme\Model\Design\Config\Storage.
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Design\Config\Storage
     */
    private $storage;

    protected function setUp(): void
    {
        $this->storage = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design\Config\Storage::class
        );
    }

    /**
     * Test design/header/welcome if it is saved in db as empty(null) it should be shown on backend as empty.
     *
     * @magentoDataFixture Magento/Theme/_files/config_data.php
     */
    public function testLoad()
    {
        $data = $this->storage->load('stores', 1);
        foreach ($data->getExtensionAttributes()->getDesignConfigData() as $configData) {
            if ($configData->getPath() == 'design/header/welcome') {
                $this->assertSame('', $configData->getValue());
            }
        }
    }
}
