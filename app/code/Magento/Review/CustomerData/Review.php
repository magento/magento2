<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
