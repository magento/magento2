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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\AdminNotification\Model\System\Message\Media;

abstract class AbstractSynchronization implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Core\Model\File\Storage\Flag
     */
    protected $_syncFlag;

    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity;

    /**
     * Is displayed flag
     *
     * @var bool
     */
    protected $_isDisplayed = null;

    /**
     * @param \Magento\Core\Model\File\Storage\Flag $fileStorage
     */
    public function __construct(\Magento\Core\Model\File\Storage\Flag $fileStorage)
    {
        $this->_syncFlag = $fileStorage->loadSelf();
    }

    /**
     * Check if message should be displayed
     *
     * @return bool
     */
    abstract protected function _shouldBeDisplayed();

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if (null === $this->_isDisplayed) {
            $output = $this->_shouldBeDisplayed();
            if ($output) {
                $this->_syncFlag->setState(\Magento\Core\Model\File\Storage\Flag::STATE_NOTIFIED);
                $this->_syncFlag->save();
            }
            $this->_isDisplayed = $output;
        }
        return $this->_isDisplayed;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;
    }
}
