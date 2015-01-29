<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\DataProvider\Page\Options;

use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Magento\Ui\Component\Listing\OptionsInterface;

/**
 * Class PageLayout
 */
class PageLayout implements OptionsInterface
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * Constructor
     *
     * @param BuilderInterface $pageLayoutBuilder
     */
    public function __construct(BuilderInterface $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * Get options
     *
     * @param array $options
     * @return array
     */
    public function getOptions(array $options = [])
    {
        $newOptions = $this->pageLayoutBuilder->getPageLayoutsConfig()->getOptions();
        foreach ($newOptions as $key => $value) {
            $newOptions[$key] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return array_merge_recursive($newOptions, $options);
    }
}
