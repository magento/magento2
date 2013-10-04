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
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Rss data helper
 *
 * @category   Magento
 * @package    Magento_Rss
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rss\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
{
    /**
     * Catalog product flat
     *
     * @var \Magento\Catalog\Helper\Product\Flat
     */
    protected $_catalogProductFlat;

    /**
     * @var \Magento\Core\Model\App\EmulationFactory
     */
    protected $_emulationFactory;

    /**
     * @param \Magento\Catalog\Helper\Product\Flat $catalogProductFlat
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Core\Model\App\EmulationFactory $emulationFactory
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Flat $catalogProductFlat,
        \Magento\Core\Helper\Context $context,
        \Magento\Core\Model\App\EmulationFactory $emulationFactory
    ) {
        $this->_catalogProductFlat = $catalogProductFlat;
        $this->_emulationFactory = $emulationFactory;
        parent::__construct($context);
    }

    /**
     * Disable using of flat catalog and/or product model to prevent limiting results to single store. Probably won't
     * work inside a controller.
     *
     * @return null
     */
    public function disableFlat()
    {
        if ($this->_catalogProductFlat->isAvailable()) {
            /* @var $emulationModel \Magento\Core\Model\App\Emulation */
            $emulationModel = $this->_emulationFactory->create();
            // Emulate admin environment to disable using flat model - otherwise we won't get global stats
            // for all stores
            $emulationModel->startEnvironmentEmulation(0, \Magento\Core\Model\App\Area::AREA_ADMIN);
        }
    }
}
