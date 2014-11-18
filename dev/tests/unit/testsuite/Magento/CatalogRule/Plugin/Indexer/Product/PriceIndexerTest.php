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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogRule\Plugin\Indexer\Product;

use Magento\TestFramework\Helper\ObjectManager;

class PriceIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceProcessor;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\Product\PriceIndexer
     */
    protected $plugin;

    protected function setUp()
    {
        $this->priceProcessor = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Price\Processor',
            [],
            [],
            '',
            false
        );
        $this->subject = $this->getMock('Magento\CatalogRule\Model\Indexer\IndexBuilder', [], [], '', false);

        $this->plugin = (new ObjectManager($this))->getObject(
            'Magento\CatalogRule\Plugin\Indexer\Product\PriceIndexer',
            [
                'priceProcessor' => $this->priceProcessor,
            ]
        );
    }

    public function testAfterSaveWithoutAffectedProductIds()
    {
        $this->priceProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->plugin->afterReindexFull($this->subject, $this->subject);
    }

    public function testReindexRow()
    {
        $productIds = [1,2,3];
        $proceed = function () {
            return;
        };
        $this->priceProcessor->expects($this->once())->method('reindexList')->with($productIds);
        $this->plugin->aroundReindexByIds($this->subject, $proceed, $productIds);
    }

    public function testReindexRows()
    {
        $productId = 1;
        $this->priceProcessor->expects($this->once())->method('reindexRow')->with($productId);
        $proceed = function () {
            return;
        };
        $this->plugin->aroundReindexById($this->subject, $proceed, $productId);
    }
}
