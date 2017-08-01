<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Review section
 * @since 2.0.0
 */
class Review implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\Session\Generic
     * @since 2.0.0
     */
    protected $reviewSession;

    /**
     * @param \Magento\Framework\Session\Generic $reviewSession
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Session\Generic $reviewSession)
    {
        $this->reviewSession = $reviewSession;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSectionData()
    {
        return (array)$this->reviewSession->getFormData(true) + ['nickname' => '','title' => '', 'detail' => ''];
    }
}
