<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend;

use Magento\TestFramework\Helper\Bootstrap;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Backend\File
     */
    private $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Config\Model\Config\Backend\File::class
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     */
    public function testFileSave()
    {
        $groups = [
            'identity' => [
                'fields' => [
                    'logo' => [
                        'value' => [
                            'value' => 'example.png'
                        ],
                    ],
                ],
            ],
        ];
        $expected = [
            'sales/identity/logo' => 'example.png'
        ];
        $section = 'sales';
        $objectManager = Bootstrap::getObjectManager();

        /** @var $_configDataObject \Magento\Config\Model\Config */
        $_configDataObject = $objectManager->create(\Magento\Config\Model\Config::class);
        $_configDataObject->setSection($section)->setWebsite('base')->setGroups($groups)->save();

        $_configDataObject = $objectManager->create(\Magento\Config\Model\Config::class);
        $_configData = $_configDataObject->setSection('sales/identity')->setWebsite('base')->load();

        $this->assertEquals($expected, $_configData);
    }
}
