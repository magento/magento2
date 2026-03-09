<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Review\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Review section
 */
class Review implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $reviewSession;

    /**
     * @param \Magento\Framework\Session\Generic $reviewSession
     */
    public function __construct(\Magento\Framework\Session\Generic $reviewSession)
    {
        $this->reviewSession = $reviewSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return (array)$this->reviewSession->getFormData(true) + ['nickname' => '','title' => '', 'detail' => ''];
    }
}
