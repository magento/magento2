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

class CustomerGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleProductProcessor;

    /**
     * @var \Magento\Customer\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\CatalogRule\Plugin\Indexer\CustomerGroup
     */
    protected $plugin;

    protected function setUp()
    {
        $this->ruleProductProcessor = $this->getMock('Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor',
            [], [], '', false);
        $this->subject = $this->getMock('Magento\Customer\Model\Group', [], [], '', false);

        $this->plugin = (new ObjectManager($this))->getObject('Magento\CatalogRule\Plugin\Indexer\CustomerGroup', [
            'ruleProductProcessor' => $this->ruleProductProcessor,
        ]);
    }

    public function testAfterDelete()
    {
        $this->ruleProductProcessor->expects($this->once())->method('markIndexerAsInvalid');

        $this->assertEquals($this->subject, $this->plugin->afterDelete($this->subject, $this->subject));
    }
}
