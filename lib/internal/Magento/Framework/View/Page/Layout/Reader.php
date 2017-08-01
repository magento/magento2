<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page\Layout;

use Magento\Framework\View\Layout;

/**
 * Class Page layout reader
 * @since 2.0.0
 */
class Reader
{
    /**
     * Merge cache suffix
     */
    const MERGE_CACHE_SUFFIX = 'page_layout';

    /**
     * @var \Magento\Framework\View\Design\Theme\ResolverInterface
     * @since 2.0.0
     */
    protected $themeResolver;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorFactory
     * @since 2.0.0
     */
    protected $processorFactory;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     * @since 2.0.0
     */
    protected $pageLayoutFileSource;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface
     * @since 2.0.0
     */
    protected $pageLayoutMerge;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool
     * @since 2.0.0
     */
    protected $reader;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\Theme\ResolverInterface $themeResolver
     * @param \Magento\Framework\View\Layout\ProcessorFactory $processorFactory
     * @param \Magento\Framework\View\File\CollectorInterface $pageLayoutFileSource
     * @param \Magento\Framework\View\Layout\ReaderPool $reader
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function read(Layout\Reader\Context $readerContext, $pageLayout)
    {
        $this->getPageLayoutMerge()->load($pageLayout);
        $xml = $this->getPageLayoutMerge()->asSimplexml();
        $this->reader->interpret($readerContext, $xml);
    }
}
