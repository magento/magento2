<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test for \Magento\Core\Model\File\Storage::getScriptConfig()
     *
     * @magentoConfigFixture current_store system/media_storage_configuration/configuration_update_time 1000
     */
    public function testGetScriptConfig()
    {
        $config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\File\Storage'
        )->getScriptConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('media_directory', $config);
        $this->assertArrayHasKey('allowed_resources', $config);
        $this->assertArrayHasKey('update_time', $config);
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\App\Filesystem'
            )->getPath(
                \Magento\Framework\App\Filesystem::MEDIA_DIR
            ),
            $config['media_directory']
        );
        $this->assertInternalType('array', $config['allowed_resources']);
        $this->assertContains('css', $config['allowed_resources']);
        $this->assertContains('css_secure', $config['allowed_resources']);
        $this->assertContains('js', $config['allowed_resources']);
        $this->assertContains('theme', $config['allowed_resources']);
        $this->assertEquals(1000, $config['update_time']);
    }
}
