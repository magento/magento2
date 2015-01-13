<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page\Layout;

use Magento\Framework\View\Layout;

/**
 * Class Page layout reader
 */
class Reader
{
    /**
     * Merge cache suffix
     */
    const MERGE_CACHE_SUFFIX = 'page_layout';

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface
     */
    protected $themeResolver;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory
     */
    protected $processorFactory;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $pageLayoutFileSource;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface
     */
    protected $pageLayoutMerge;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool
     */
    protected $reader;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver
     * @param \Magento\Framework\View\Layout\ProcessorFactory $processorFactory
     * @param \Magento\Framework\View\File\CollectorInterface $pageLayoutFileSource
     * @param \Magento\Framework\View\Layout\ReaderPool $reader
     */
    public function __construct(
        \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver,
        \Magento\Framework\View\Layout\ProcessorFactory $processorFactory,
        \Magento\Framework\View\File\CollectorInterface $pageLayoutFileSource,
        \Magento\Framework\View\Layout\ReaderPool $reader
    ) {
        $this->themeResolver = $themeResolver;
        $this->processorFactory = $processorFactory;
        $this->pageLayoutFileSource = $pageLayoutFileSource;
        $this->reader = $reader;
    }

    /**
     * Retrieve the layout update instance
     *
     * @return \Magento\Framework\View\Layout\ProcessorInterface
     */
    protected function getPageLayoutMerge()
    {
        if ($this->pageLayoutMerge) {
            return $this->pageLayoutMerge;
        }
        $this->pageLayoutMerge = $this->processorFactory->create([
            'theme'       => $this->themeResolver->get(),
            'fileSource'  => $this->pageLayoutFileSource,
            'cacheSuffix' => self::MERGE_CACHE_SUFFIX,
        ]);
        return $this->pageLayoutMerge;
    }

    /**
     * Read page layout structure and fill reader context
     *
     * @param Layout\Reader\Context $readerContext
     * @param string $pageLayout
     * @return void
     */
    public function read(Layout\Reader\Context $readerContext, $pageLayout)
    {
        $this->getPageLayoutMerge()->load($pageLayout);
        $xml = $this->getPageLayoutMerge()->asSimplexml();
        $this->reader->interpret($readerContext, $xml);
    }
}
