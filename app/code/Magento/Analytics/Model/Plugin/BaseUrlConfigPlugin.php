<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Plugin;

use Magento\Analytics\Model\FlagManager;

/**
 * Class BaseUrlConfigPlugin
 *
 * Plugin checks if value changed than save old base url and call subscription update.
 */
class BaseUrlConfigPlugin
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    private $oldUrlFlagCode = 'analytics_old_base_url';

    /**
     * @param FlagManager $flagManager
     */
    public function __construct(FlagManager $flagManager)
    {
        $this->flagManager = $flagManager;
    }

    /**
     * Invalidate WebApi cache if needed.
     *
     * @param \Magento\Framework\App\Config\Value $subject
     * @param \Magento\Framework\App\Config\Value $result
     * @return \Magento\Framework\App\Config\Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(
        \Magento\Framework\App\Config\Value $subject,
        \Magento\Framework\App\Config\Value $result
    ) {
        if ($result->isValueChanged()) {
            $this->flagManager->saveFlag($this->oldUrlFlagCode, $result->getOldValue());
        }

        return $result;
    }
}
