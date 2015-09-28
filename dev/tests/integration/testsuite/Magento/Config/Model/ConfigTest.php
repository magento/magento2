<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Config\Model\Config::save
     * @param array $groups
     * @magentoDbIsolation enabled
     * @dataProvider saveWithSingleStoreModeEnabledDataProvider
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testSaveWithSingleStoreModeEnabled($groups)
    {
        Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        /** @var $_configDataObject \Magento\Config\Model\Config */
        $_configDataObject = Bootstrap::getObjectManager()->create('Magento\Config\Model\Config');
        $_configData = $_configDataObject->setSection('dev')->setWebsite('base')->load();
        $this->assertEmpty($_configData);

        $_configDataObject = Bootstrap::getObjectManager()->create('Magento\Config\Model\Config');
        $_configDataObject->setSection('dev')->setGroups($groups)->save();

        /** @var $_configDataObject \Magento\Config\Model\Config */
        $_configDataObject = Bootstrap::getObjectManager()->create('Magento\Config\Model\Config');
        $_configData = $_configDataObject->setSection('dev')->load();
        $this->assertArrayHasKey('dev/debug/template_hints_admin', $_configData);
        $this->assertArrayHasKey('dev/debug/template_hints_blocks', $_configData);

        $_configDataObject = Bootstrap::getObjectManager()->create('Magento\Config\Model\Config');
        $_configData = $_configDataObject->setSection('dev')->setWebsite('base')->load();
        $this->assertArrayNotHasKey('dev/debug/template_hints_admin', $_configData);
        $this->assertArrayNotHasKey('dev/debug/template_hints_blocks', $_configData);
    }

    public function saveWithSingleStoreModeEnabledDataProvider()
    {
        return require __DIR__ . '/_files/config_groups.php';
    }

    /**
     * @covers \Magento\Config\Model\Config::save
     * @param string $section
     * @param array $groups
     * @param array $expected
     * @magentoDbIsolation enabled
     * @dataProvider saveDataProvider
     */
    public function testSave($section, $groups, $expected)
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var $_configDataObject \Magento\Config\Model\Config */
        $_configDataObject = $objectManager->create('Magento\Config\Model\Config');
        $_configDataObject->setSection($section)->setWebsite('base')->setGroups($groups)->save();

        foreach ($expected as $group => $expectedData) {
            $_configDataObject = $objectManager->create('Magento\Config\Model\Config');
            $_configData = $_configDataObject->setSection($group)->setWebsite('base')->load();
            if (array_key_exists('payment/payflow_link/pwd', $_configData)) {
                $_configData['payment/payflow_link/pwd'] = $objectManager->get(
                    'Magento\Framework\Encryption\EncryptorInterface'
                )->decrypt(
                    $_configData['payment/payflow_link/pwd']
                );
            }
            $this->assertEquals($expectedData, $_configData);
        }
    }

    public function saveDataProvider()
    {
        return require __DIR__ . '/_files/config_section.php';
    }
}
