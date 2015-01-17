<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class MenuConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Config\Reader
     */
    protected $_model;

    protected function setUp()
    {
        $moduleReader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Module\Dir\Reader'
        );
        $schemaFile = $moduleReader->getModuleDir('etc', 'Magento_Backend') . '/menu.xsd';
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Menu\Config\Reader',
            ['perFileSchema' => $schemaFile, 'isValidated' => true]
        );
    }

    public function testValidateMenuFiles()
    {
        $this->_model->read('adminhtml');
    }
}
