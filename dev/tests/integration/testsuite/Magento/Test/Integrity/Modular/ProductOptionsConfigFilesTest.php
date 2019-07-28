<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\Component\ComponentRegistrar;

class ProductOptionsConfigFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductOptions\Config\Reader
     */
    protected $_model;

    protected function setUp()
    {
        //init primary configs
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $moduleDirSearch \Magento\Framework\Component\DirSearch */
        $moduleDirSearch = $objectManager->get(\Magento\Framework\Component\DirSearch::class);
        $fileIteratorFactory = $objectManager->get(\Magento\Framework\Config\FileIteratorFactory::class);
        $xmlFiles = $fileIteratorFactory->create(
            $moduleDirSearch->collectFiles(
                ComponentRegistrar::MODULE,
                'etc/{*/product_options.xml,product_options.xml}'
            )
        );

        $fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($xmlFiles));
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->expects($this->any())->method('isValidationRequired')->will($this->returnValue(true));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create(
            \Magento\Catalog\Model\ProductOptions\Config\Reader::class,
            ['fileResolver' => $fileResolverMock, 'validationState' => $validationStateMock]
        );
    }

    public function testProductOptionsXmlFiles()
    {
        $this->_model->read('global');
    }
}
