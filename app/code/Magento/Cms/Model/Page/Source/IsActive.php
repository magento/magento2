<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Page\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 * @since 2.0.0
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var \Magento\Cms\Model\Page
     * @since 2.0.0
     */
    protected $cmsPage;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     * @since 2.0.0
     */
    public function __construct(\Magento\Cms\Model\Page $cmsPage)
    {
        $this->cmsPage = $cmsPage;
    }

    /**
     * Get options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $availableOptions = $this->cmsPage->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
