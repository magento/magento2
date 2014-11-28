<?php
/**
 * \Magento\Framework\Object\Copy\Config\Reader
 *
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
namespace Magento\Framework\Object\Copy\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_disabled.php
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy\Config\Reader
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
            'Magento\Framework\Object\Copy\Config\Reader',
            array('fileResolver' => $this->fileResolver)
        );
    }

    public function testRead()
    {
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('fieldset.xml', 'global')
            ->willReturn([file_get_contents(__DIR__ . '/_files/fieldset.xml')]);
        $expected = include __DIR__ . '/_files/expectedArray.php';
        $this->assertEquals($expected, $this->model->read('global'));
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = [
            file_get_contents(__DIR__ . '/_files/partialFieldsetFirst.xml'),
            file_get_contents(__DIR__ . '/_files/partialFieldsetSecond.xml')
        ];
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('fieldset.xml', 'global')
            ->willReturn($fileList);
        $expected = array(
            'global' => array(
                'sales_convert_quote_item' => array(
                    'event_id' => array('to_order_item' => "*"),
                    'event_name' => array('to_order_item' => "*"),
                    'event_description' => array('to_order_item' => "complexDesciption")
                )
            )
        );
        $this->assertEquals($expected, $this->model->read('global'));
    }
}
