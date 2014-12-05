<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            'cacheSuffix' => self::MERGE_CACHE_SUFFIX
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
