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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog flat abstract helper
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Helper\Flat;

abstract class AbstractFlat extends \Magento\Core\Helper\AbstractHelper
{
    /**
     * Catalog Flat index process code
     *
     * @var null|string
     */
    protected $_indexerCode = null;

    /**
     * Store catalog Flat index process instance
     *
     * @var \Magento\Index\Model\Process|null
     */
    protected $_process = null;

    /**
     * Check if Catalog Flat Data has been initialized
     *
     * @return bool
     */
    abstract public function isBuilt();

    /**
     * Check if Catalog Category Flat Data is enabled
     *
     * @param mixed $deprecatedParam this parameter is deprecated and no longer in use
     *
     * @return bool
     */
    abstract public function isEnabled($deprecatedParam = false);

    /**
     * Process factory
     *
     * @var \Magento\Index\Model\ProcessFactory
     */
    protected $_processFactory;

    /**
     * Construct
     *
     * @param \Magento\Index\Model\ProcessFactory $processFactory
     * @param \Magento\Core\Helper\Context $context
     */
    public function __construct(
        \Magento\Index\Model\ProcessFactory $processFactory,
        \Magento\Core\Helper\Context $context
    ) {
        $this->_processFactory = $processFactory;
        parent::__construct($context);
    }

    /**
     * Check if Catalog Category Flat Data is available for use
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->isEnabled() && !$this->getProcess()->isLocked()
            && $this->getProcess()->getStatus() != \Magento\Index\Model\Process::STATUS_RUNNING;
    }

    /**
     * Retrieve Catalog Flat index process
     *
     * @return \Magento\Index\Model\Process
     */
    public function getProcess()
    {
        if (is_null($this->_process)) {
            $this->_process = $this->_processFactory->create()
                ->load($this->_indexerCode, 'indexer_code');
        }
        return $this->_process;
    }
}
