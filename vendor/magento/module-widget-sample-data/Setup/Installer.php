<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WidgetSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\WidgetSampleData\Model\CmsBlock
     */
    protected $cmsBlock;

    /**
     * @param \Magento\WidgetSampleData\Model\CmsBlock $cmsBlock
     */
    public function __construct(\Magento\WidgetSampleData\Model\CmsBlock $cmsBlock) {
        $this->cmsBlock = $cmsBlock;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->cmsBlock->install(
            [
                'Magento_WidgetSampleData::fixtures/cmsblock.csv',
                'Magento_WidgetSampleData::fixtures/cmsblock_giftcard.csv'
            ]
        );
    }
}