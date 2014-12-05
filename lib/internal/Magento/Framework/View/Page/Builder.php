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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Page;

use Magento\Framework\App;
use Magento\Framework\View;
use Magento\Framework\Event;
use Magento\Framework\Profiler;

/**
 * Class Builder
 */
class Builder extends View\Layout\Builder
{
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Page\Layout\Reader
     */
    protected $pageLayoutReader;

    /**
     * @param View\LayoutInterface $layout
     * @param App\Request\Http $request
     * @param Event\ManagerInterface $eventManager
     * @param Config $pageConfig
     * @param Layout\Reader $pageLayoutReader
     */
    public function __construct(
        View\LayoutInterface $layout,
        App\Request\Http $request,
        Event\ManagerInterface $eventManager,
        Config $pageConfig,
        Layout\Reader $pageLayoutReader
    ) {
        parent::__construct($layout, $request, $eventManager);
        $this->pageConfig = $pageConfig;
        $this->pageLayoutReader = $pageLayoutReader;
        $this->pageConfig->setBuilder($this);
    }

    /**
     * Read page layout before generation generic layout
     *
     * @return $this
     */
    protected function generateLayoutBlocks()
    {
        $this->readPageLayout();
        return parent::generateLayoutBlocks();
    }

    /**
     * Read page layout and write structure to ReadContext
     * @return void
     */
    protected function readPageLayout()
    {
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $readerContext = $this->layout->getReaderContext();
            $this->pageLayoutReader->read($readerContext, $pageLayout);
        }
    }

    /**
     * @return string
     */
    protected function getPageLayout()
    {
        return $this->pageConfig->getPageLayout() ?: $this->layout->getUpdate()->getPageLayout();
    }
}
