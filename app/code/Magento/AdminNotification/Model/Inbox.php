<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model;

use Magento\AdminNotification\Model\ResourceModel\Inbox as InboxResourceModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Notification\NotifierInterface;

/**
 * AdminNotification Inbox model
 *
 * @method int getSeverity()
 * @method Inbox setSeverity(int $value)
 * @method string getDateAdded()
 * @method Inbox setDateAdded(string $value)
 * @method string getTitle()
 * @method Inbox setTitle(string $value)
 * @method string getDescription()
 * @method Inbox setDescription(string $value)
 * @method string getUrl()
 * @method Inbox setUrl(string $value)
 * @method int getIsRead()
 * @method Inbox setIsRead(int $value)
 * @method int getIsRemove()
 * @method Inbox setIsRemove(int $value)
 *
 * @package Magento\AdminNotification\Model
 * @api
 * @since 100.0.2
 */
class Inbox extends AbstractModel implements NotifierInterface, InboxInterface
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_init(InboxResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getSeverities($severity = null)
    {
        $severities = [
            MessageInterface::SEVERITY_CRITICAL => __('critical'),
            MessageInterface::SEVERITY_MAJOR => __('major'),
            MessageInterface::SEVERITY_MINOR => __('minor'),
            MessageInterface::SEVERITY_NOTICE => __('notice'),
        ];

        if ($severity !== null) {
            if (isset($severities[$severity])) {
                return $severities[$severity];
            }
            return null;
        }

        return $severities;
    }

    /**
     * @inheritdoc
     */
    public function loadLatestNotice()
    {
        $this->setData([]);
        $this->getResource()->loadLatestNotice($this);
        return $this;
    }

    /**
     * {@inheritdoc}
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
     * @throws LocalizedException
     * @return $this
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function add($severity, $title, $description, $url = '', $isInternal = true)
    {
        if (!$this->getSeverities($severity)) {
            throw new LocalizedException(__('Wrong message type'));
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
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
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
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
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
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
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
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function addNotice($title, $description, $url = '', $isInternal = true)
    {
        $this->add(MessageInterface::SEVERITY_NOTICE, $title, $description, $url, $isInternal);
        return $this;
    }
}
