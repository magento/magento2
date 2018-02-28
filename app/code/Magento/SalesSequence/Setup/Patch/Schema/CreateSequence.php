<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesSequence\Setup\Patch\Schema;

use Magento\Framework\App\State;
use Magento\SalesSequence\Setup\SequenceCreator;
use Magento\Setup\Model\Patch\PatchVersionInterface;
use Magento\Setup\Model\Patch\SchemaPatchInterface;

/**
 * Class CreateSequence
 * @package Magento\SalesSequence\Setup\Patch
 */
class CreateSequence implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SequenceCreator
     */
    private $sequenceCreator;

    /**
     * CreateSequence constructor.
     * @param SequenceCreator $sequenceCreator
     */
    public function __construct(
        SequenceCreator $sequenceCreator
    ) {
        $this->sequenceCreator = $sequenceCreator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->sequenceCreator->create();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            \Magento\Store\Setup\Patch\Schema\InitializeStoresAndWebsites::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
