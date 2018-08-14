<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\GlobalSearch;

/**
 * Entity for global search in backend
 */
class SearchEntity extends \Magento\Framework\DataObject
{
    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getData('url');
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Set Id.
     *
     * @param string $value
     */
    public function setId(string $value)
    {
        $this->setData('id', $value);
    }

    /**
     * Set url.
     *
     * @param string $value
     */
    public function setUrl(string $value)
    {
        $this->setData('url', $value);
    }

    /**
     * Set title.
     *
     * @param string $value
     */
    public function setTitle(string $value)
    {
        $this->setData('title', $value);
    }
}
