<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Staging theme model class
 */
namespace Magento\Theme\Model\Theme\Domain;

use Magento\Framework\View\Design\Theme\Domain\StagingInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\CopyService;

class Staging implements StagingInterface
{
    /**
     * Staging theme model instance
     *
     * @var ThemeInterface
     */
    protected $_theme;

    /**
     * @var CopyService
     */
    protected $_themeCopyService;

    /**
     * @param ThemeInterface $theme
     * @param CopyService $themeCopyService
     */
    public function __construct(
        ThemeInterface $theme,
        CopyService $themeCopyService
    ) {
        $this->_theme = $theme;
        $this->_themeCopyService = $themeCopyService;
    }

    /**
     * Copy changes from 'staging' theme
     *
     * @return StagingInterface
     */
    public function updateFromStagingTheme()
    {
        $this->_themeCopyService->copy($this->_theme, $this->_theme->getParentTheme());
        return $this;
    }
}
