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
namespace Magento\CatalogRule\Plugin\Indexer;

use Magento\TestFramework\Helper\ObjectManager;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRuleProcessor;

    /**
     * @var \Magento\Catalog\Model\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\Category
     */
    protected $plugin;

    protected function setUp()
    {
        $this->productRuleProcessor = $this->getMock('Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor',
            [], [], '', false);
        $this->subject = $this->getMock('Magento\Catalog\Model\Category', ['getAffectedProductIds', '__wakeUp'], [],
            '', false);

        $this->plugin = (new ObjectManager($this))->getObject('Magento\CatalogRule\Plugin\Indexer\Category', [
            'productRuleProcessor' => $this->productRuleProcessor,
        ]);
    }

    public function testAfterSaveWithoutAffectedProductIds()
    {
        $this->subject->expects($this->any())->method('getAffectedProductIds')->will($this->returnValue([]));
        $this->productRuleProcessor->expects($this->never())->method('reindexList');

        $this->assertEquals($this->subject, $this->plugin->afterSave($this->subject, $this->subject));
    }

    public function testAfterSave()
    {
        $productIds = [1, 2, 3];

        $this->subject->expects($this->any())->method('getAffectedProductIds')->will($this->returnValue($productIds));
        $this->productRuleProcessor->expects($this->once())->method('reindexList')->with($productIds);

        $this->assertEquals($this->subject, $this->plugin->afterSave($this->subject, $this->subject));
    }

    public function testAfterDelete()
    {
        $this->productRuleProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertEquals($this->subject, $this->plugin->afterDelete($this->subject, $this->subject));
    }
}
