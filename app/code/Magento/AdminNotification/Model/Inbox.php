<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Notification\NotifierInterface;

/**
 * AdminNotification Inbox model
 *
 * @method \Magento\AdminNotification\Model\Resource\Inbox _getResource()
 * @method \Magento\AdminNotification\Model\Resource\Inbox getResource()
 * @method int getSeverity()
 * @method \Magento\AdminNotification\Model\Inbox setSeverity(int $value)
 * @method string getDateAdded()
 * @method \Magento\AdminNotification\Model\Inbox setDateAdded(string $value)
 * @method string getTitle()
 * @method \Magento\AdminNotification\Model\Inbox setTitle(string $value)
 * @method string getDescription()
 * @method \Magento\AdminNotification\Model\Inbox setDescription(string $value)
 * @method string getUrl()
 * @method \Magento\AdminNotification\Model\Inbox setUrl(string $value)
 * @method int getIsRead()
 * @method \Magento\AdminNotification\Model\Inbox setIsRead(int $value)
 * @method int getIsRemove()
 * @method \Magento\AdminNotification\Model\Inbox setIsRemove(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Inbox extends \Magento\Framework\Model\AbstractModel implements NotifierInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\AdminNotification\Model\Resource\Inbox');
    }

    /**
     * Retrieve Severity collection array
     *
     * @param int|null $severity
     * @return array|string|null
     */
    public function getSeverities($severity = null)
    {
        $severities = [
            MessageInterface::SEVERITY_CRITICAL => __('critical'),
            MessageInterface::SEVERITY_MAJOR => __('major'),
            MessageInterface::SEVERITY_MINOR => __('minor'),
            MessageInterface::SEVERITY_NOTICE => __('notice'),
        ];

        if (!is_null($severity)) {
            if (isset($severities[$severity])) {
                return $severities[$severity];
            }
            return null;
        }

        return $severities;
    }

    /**
     * Retrieve Latest Notice
     *
     * @return $this
     */
    public function loadLatestNotice()
    {
        $this->setData([]);
        $this->getResource()->loadLatestNotice($this);
        return $this;
    }

    /**
     * Retrieve notice statuses
     *
     * @return array
     */
    public function getNoticeStatus()
    {
        return $this->getResource()->getNoticeStatus($this);
    }

    /**
     * Parse and save new data
     *
     * @param array $data
     * @return $this
     */
    public function parse(array $data)
    {
        $this->getResource()->parse($this, $data);
        return $this;
    }

    /**
     * Add new message
     *
     * @param int $severity
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function add($severity, $title, $description, $url = '', $isInternal = true)
    {
        if (!$this->getSeverities($severity)) {
            throw new \Magento\Framework\Model\Exception(__('Wrong message type'));
        }
        if (is_array($description)) {
            $description = '<ul><li>' . implode('</li><li>', $description) . '</li></ul>';
        }
        $date = date('Y-m-d H:i:s');
        $this->parse(
            [
                [
                    'severity' => $severity,
                    'date_added' => $date,
                    'title' => $title,
                    'description' => $description,
                    'url' => $url,
                    'internal' => $isInternal,
                ],
            ]
        );
        return $this;
    }

    /**
     * Add critical severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addCritical($title, $description, $url = '', $isInternal = true)
    {
        $this->add(MessageInterface::SEVERITY_CRITICAL, $title, $description, $url, $isInternal);
        return $this;
    }

    /**
     * Add major severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addMajor($title, $description, $url = '', $isInternal = true)
    {
        $this->add(MessageInterface::SEVERITY_MAJOR, $title, $description, $url, $isInternal);
        return $this;
    }

    /**
     * Add minor severity message
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addMinor($title, $description, $url = '', $isInternal = true)
    {
        $this->add(MessageInterface::SEVERITY_MINOR, $title, $description, $url, $isInternal);
        return $this;
    }

    /**
     * Add notice
     *
     * @param string $title
     * @param string|string[] $description
     * @param string $url
     * @param bool $isInternal
     * @return $this
     */
    public function addNotice($title, $description, $url = '', $isInternal = true)
    {
        $this->add(MessageInterface::SEVERITY_NOTICE, $title, $description, $url, $isInternal);
        return $this;
    }
}
