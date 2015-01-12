<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer;

use Magento\TestFramework\Helper\ObjectManager;

class ImportExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleProductProcessor;

    /**
     * @var \Magento\ImportExport\Model\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\ImportExport
     */
    protected $plugin;

    protected function setUp()
    {
        $this->ruleProductProcessor = $this->getMock('Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor',
            [], [], '', false);
        $this->subject = $this->getMock('Magento\ImportExport\Model\Import', [], [], '', false);

        $this->plugin = (new ObjectManager($this))->getObject('Magento\CatalogRule\Plugin\Indexer\ImportExport', [
            'ruleProductProcessor' => $this->ruleProductProcessor,
        ]);
    }

    public function testAfterImportSource()
    {
        $result = true;

        $this->ruleProductProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertEquals($result, $this->plugin->afterImportSource($this->subject, $result));
    }
}
