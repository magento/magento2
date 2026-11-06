<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SequenceTest extends TestCase
{
    /**
     * @var Sequence
     */
    private $sequence;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->storeId = (int)$objectManager->get(StoreManagerInterface::class)->getStore('default')->getId();
        $this->sequence = $objectManager->create(Manager::class)->getSequence('order', $this->storeId);
    }

    /**
     * @return void
     */
    #[DbIsolation(true)]
    public function testExposesContextOfOrderSequence(): void
    {
        $this->assertNull($this->sequence->calculateCurrentValue());

        $value = $this->sequence->getNextValue();
        $profile = $this->sequence->getMetaProfile();

        $this->assertSame('order', $this->sequence->getEntityType());
        $this->assertSame($this->storeId, $this->sequence->getStoreId());
        $this->assertSame(
            sprintf(
                Sequence::DEFAULT_PATTERN,
                $profile->getPrefix(),
                $this->sequence->calculateCurrentValue(),
                $profile->getSuffix()
            ),
            $value
        );
    }
}
