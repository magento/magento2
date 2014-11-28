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
namespace Magento\Widget\Model\Config;

/**
 * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_disabled.php
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Reader
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileResolver;

    public function setUp()
    {
        $this->fileResolver = $this->getMockForAbstractClass('Magento\Framework\Config\FileResolverInterface');
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create(
            'Magento\Widget\Model\Config\Reader', ['fileResolver' => $this->fileResolver]
        );
    }

    public function testRead()
    {
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->willReturn([file_get_contents(__DIR__ . '/_files/orders_and_returns.xml')]);
        $expected = include __DIR__ . '/_files/expectedGlobalArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }

    public function testReadFile()
    {
        $file = file_get_contents(__DIR__ . '/_files/orders_and_returns.xml');
        $expected = include __DIR__ . '/_files/expectedGlobalArray.php';
        $this->assertEquals($expected, $this->model->readFile($file));
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = [
            file_get_contents(__DIR__ . '/_files/catalog_new_products_list.xml'),
            file_get_contents(__DIR__ . '/_files/orders_and_returns_customized.xml'),
        ];
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('widget.xml', 'global')
            ->willReturn($fileList);
        $expected = include __DIR__ . '/_files/expectedMergedArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }
}
