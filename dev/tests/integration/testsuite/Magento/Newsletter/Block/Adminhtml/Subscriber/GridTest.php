<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Block\Adminhtml\Subscriber;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 *
 * @see \Magento\Newsletter\Block\Adminhtml\Subscriber\Grid
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var null|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;
    /**
     * @var null|\Magento\Framework\View\LayoutInterface
     */
    private $layout = null;

    /**
     * Set up layout.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->layout = $this->objectManager->create(\Magento\Framework\View\LayoutInterface::class);
        $this->layout->getUpdate()->load('newsletter_subscriber_grid');
        $this->layout->generateXml();
        $this->layout->generateElements();
    }

    /**
     * Check if mass action block exists.
     */
    public function testMassActionBlockExists()
    {
        $this->assertNotFalse(
            $this->getMassActionBlock(),
            'Mass action block does not exist in the grid, or it name was changed.'
        );
    }

    /**
     * Check if mass action id field is correct.
     */
    public function testMassActionFieldIdIsCorrect()
    {
        $this->assertEquals(
            'subscriber_id',
            $this->getMassActionBlock()->getMassactionIdField(),
            'Mass action id field is incorrect.'
        );
    }

    /**
     * Check if function returns correct result.
     *
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testMassActionBlockContainsCorrectIdList()
    {
        $this->assertEquals(
            implode(',', $this->getAllSubscriberIdList()),
            $this->getMassActionBlock()->getGridIdsJson(),
            'Function returns incorrect result.'
        );
    }

    /**
     * Retrieve mass action block.
     *
     * @return bool|\Magento\Backend\Block\Widget\Grid\Massaction
     */
    private function getMassActionBlock()
    {
        return $this->layout->getBlock('adminhtml.newslettrer.subscriber.grid.massaction');
    }

    /**
     * Retrieve list of id of all subscribers.
     *
     * @return array
     */
    private function getAllSubscriberIdList()
    {
        /** @var \Magento\Framework\App\ResourceConnection $resourceConnection */
        $resourceConnection = $this->objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $select = $resourceConnection->getConnection()
            ->select()
            ->from($resourceConnection->getTableName('newsletter_subscriber'))
            ->columns(['subscriber_id' => 'subscriber_id']);

        return $resourceConnection->getConnection()->fetchCol($select);
    }
}
