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
namespace Magento\Backend\Model\View\Page;

use Magento\Framework\App;
use Magento\Framework\View;
use Magento\Framework\Event;
use Magento\Backend\Model\View\Layout;

class Builder extends View\Page\Builder
{
    /**
     * @var Layout\Filter\Acl $aclFilter
     */
    protected $aclFilter;

    /**
     * @param View\LayoutInterface $layout
     * @param App\Request\Http $request
     * @param Event\ManagerInterface $eventManager
     * @param View\Page\Config $pageConfig
     * @param View\Page\Layout\Reader $pageLayoutReader
     * @param Layout\Filter\Acl $aclFilter
     */
    public function __construct(
        View\LayoutInterface $layout,
        App\Request\Http $request,
        Event\ManagerInterface $eventManager,
        View\Page\Config $pageConfig,
        View\Page\Layout\Reader $pageLayoutReader,
        Layout\Filter\Acl $aclFilter
    ) {
        parent::__construct($layout, $request, $eventManager, $pageConfig, $pageLayoutReader);
        $this->aclFilter = $aclFilter;
    }

    /**
     * @return $this
     */
    protected function beforeGenerateBlock()
    {
        $this->aclFilter->filterAclNodes($this->layout->getNode());
        return $this;
    }

    /**
     * @return $this
     */
    protected function afterGenerateBlock()
    {
        $this->layout->initMessages();
        return $this;
    }
}
