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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend Inline Translation config factory
 */
namespace Magento\Backend\Model\Translate\Inline;

class ConfigFactory extends \Magento\Core\Model\Translate\Inline\ConfigFactory
{
    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\State $appState
     */
    public function __construct(\Magento\ObjectManager $objectManager, \Magento\App\State $appState)
    {
        $this->_appState = $appState;
        parent::__construct($objectManager);
    }

    /**
     * Create instance of inline translate config
     *
     * @param string|null $area
     * @return \Magento\Core\Model\Translate\Inline\ConfigInterface
     */
    public function create($area = null)
    {
        if (!isset($area)) {
            $area = $this->_appState->getAreaCode();
        }
        if ($area == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
            return $this->_objectManager->create('Magento\Backend\Model\Translate\Inline\Config');
        }

        return parent::create();
    }
}
