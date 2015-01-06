<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Ui\DataProvider\Page\Options;

use Magento\Core\Model\PageLayout\Config\Builder;
use Magento\Ui\Component\Listing\OptionsInterface;

/**
 * Class PageLayout
 */
class PageLayout implements OptionsInterface
{
    /**
     * @var \Magento\Core\Model\PageLayout\Config\Builder
     */
    protected $pageLayoutBuilder;

    /**
     * Constructor
     *
     * @param Builder $pageLayoutBuilder
     */
    public function __construct(Builder $pageLayoutBuilder)
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
