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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Helper;

use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;

class Data extends \Magento\App\Helper\AbstractHelper
{
    /** @var \Magento\Core\Model\Registry */
    protected $_registry;

    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Registry $registry
    ) {
        $this->_registry = $registry;
        parent::__construct($context);
    }

    public function getSelectedResources()
    {
        $selectedResourceIds = array();
        $currentIntegration = $this->_registry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        if ($currentIntegration
            && isset($currentIntegration['resource']) && is_array($currentIntegration['resource'])
        ) {
            $selectedResourceIds = $currentIntegration['resource'];
        }
        return $selectedResourceIds;
    }
}
