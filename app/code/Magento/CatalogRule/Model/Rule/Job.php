<?php
/**
 * CatalogRule Rule Job model
 *
 * Uses for encapsulate some logic of rule model and for having ability change behavior (for example, in controller)
 *
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

/**
 * Catalog Rule job model
 *
 * @method \Magento\CatalogRule\Model\Rule\Job setSuccess(string $errorMessage)
 * @method \Magento\CatalogRule\Model\Rule\Job setError(string $errorMessage)
 * @method string getSuccess()
 * @method string getError()
 * @method bool hasSuccess()
 * @method bool hasError()
 *
 * @author    Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Model\Rule;

class Job extends \Magento\Framework\Object
{
    /**
     * Instance of event manager model
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Basic object initialization
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->_eventManager = $eventManager;
    }

    /**
     * Dispatch event "catalogrule_apply_all" and set success or error message depends on result
     *
     * @return \Magento\CatalogRule\Model\Rule\Job
     */
    public function applyAll()
    {
        try {
            $this->_eventManager->dispatch('catalogrule_apply_all');
            $this->setSuccess(__('The rules have been applied.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->setError($e->getMessage());
        }
        return $this;
    }
}
