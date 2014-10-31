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

namespace Magento\Framework\View\Result;

use Magento\Framework\ObjectManager;

/**
 * A factory that knows how to create a "page" result
 * Requires an instance of controller action in order to impose page type,
 * which is by convention is determined from the controller action class
 */
class PageFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @param ObjectManager $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManager $objectManager, $instanceName = 'Magento\Framework\View\Result\Page')
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create new page regarding its type
     *
     * TODO: As argument has to be controller action interface, temporary solution until controller output models
     * TODO: are not implemented
     *
     * @param bool $isView
     * @param array $arguments
     * @return \Magento\Framework\View\Result\Page
     */
    public function create($isView = false, array $arguments = [])
    {
        /** @var \Magento\Framework\View\Result\Page $page */
        $page = $this->objectManager->create($this->instanceName, $arguments);
        // TODO Temporary solution for compatibility with View object. Will be deleted in MAGETWO-28359
        if (!$isView) {
            $page->addDefaultHandle();
        }
        return $page;
    }
}
