<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Setup;

use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Put the poison pill after each potential deployment.
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * @var PoisonPillPutInterface
     */
    private $poisonPillPut;

    /**
     * @param PoisonPillPutInterface $poisonPillPut
     */
    public function __construct(PoisonPillPutInterface $poisonPillPut)
    {
        $this->poisonPillPut = $poisonPillPut;
    }

    /**
     * Put the Poison Pill after each 'setup:upgrade' command run.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->poisonPillPut->put();
    }
}
