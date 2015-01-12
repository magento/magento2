<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer;

use Magento\TestFramework\Helper\ObjectManager;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleProductProcessor;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\Website
     */
    protected $plugin;

    protected function setUp()
    {
        $this->ruleProductProcessor = $this->getMock('Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor',
            [], [], '', false);
        $this->subject = $this->getMock('Magento\Store\Model\Website', [], [], '', false);

        $this->plugin = (new ObjectManager($this))->getObject('Magento\CatalogRule\Plugin\Indexer\Website', [
            'ruleProductProcessor' => $this->ruleProductProcessor,
        ]);
    }

    public function testAfterDelete()
    {
        $this->ruleProductProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertEquals($this->subject, $this->plugin->afterDelete($this->subject, $this->subject));
    }
}
