<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\DataProvider\Page\Options;

use Magento\Ui\Component\Listing\OptionsInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionsInterface
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(\Magento\Cms\Model\Page $cmsPage)
    {
        $this->cmsPage = $cmsPage;
    }

    /**
     * Get options
     *
     * @param array $options
     * @return array
     */
    public function getOptions(array $options = [])
    {
        $newOptions = $this->cmsPage->getAvailableStatuses();
        foreach ($newOptions as $key => $value) {
            $newOptions[$key] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return array_merge_recursive($newOptions, $options);
    }
}
