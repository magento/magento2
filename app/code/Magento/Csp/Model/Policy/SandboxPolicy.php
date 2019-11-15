<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Policy;

/**
 * "sandbox" directive enables sandbox mode for requested pages limiting their functionality.
 *
 * Works the same as "sandbox" attribute for iframes but for the main document.
 */
class SandboxPolicy implements SimplePolicyInterface
{
    /**
     * @var bool
     */
    private $formAllowed;

    /**
     * @var bool
     */
    private $modalsAllowed;

    /**
     * @var bool
     */
    private $orientationLockAllowed;

    /**
     * @var bool
     */
    private $pointerLockAllowed;

    /**
     * @var bool
     */
    private $popupsAllowed;

    /**
     * @var bool
     */
    private $popupsToEscapeSandboxAllowed;

    /**
     * @var bool
     */
    private $presentationAllowed;

    /**
     * @var bool
     */
    private $sameOriginAllowed;

    /**
     * @var bool
     */
    private $scriptsAllowed;

    /**
     * @var bool
     */
    private $topNavigationAllowed;

    /**
     * @var bool
     */
    private $topNavigationByUserActivationAllowed;

    /**
     * @param bool $formAllowed
     * @param bool $modalsAllowed
     * @param bool $orientationLockAllowed
     * @param bool $pointerLockAllowed
     * @param bool $popupsAllowed
     * @param bool $popupsToEscapeSandboxAllowed
     * @param bool $presentationAllowed
     * @param bool $sameOriginAllowed
     * @param bool $scriptsAllowed
     * @param bool $topNavigationAllowed
     * @param bool $topNavigationByUserActivationAllowed
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        bool $formAllowed,
        bool $modalsAllowed,
        bool $orientationLockAllowed,
        bool $pointerLockAllowed,
        bool $popupsAllowed,
        bool $popupsToEscapeSandboxAllowed,
        bool $presentationAllowed,
        bool $sameOriginAllowed,
        bool $scriptsAllowed,
        bool $topNavigationAllowed,
        bool $topNavigationByUserActivationAllowed
    ) {
        $this->formAllowed = $formAllowed;
        $this->modalsAllowed = $modalsAllowed;
        $this->orientationLockAllowed = $orientationLockAllowed;
        $this->pointerLockAllowed = $pointerLockAllowed;
        $this->popupsAllowed = $popupsAllowed;
        $this->popupsToEscapeSandboxAllowed = $popupsToEscapeSandboxAllowed;
        $this->presentationAllowed = $presentationAllowed;
        $this->sameOriginAllowed = $sameOriginAllowed;
        $this->scriptsAllowed = $scriptsAllowed;
        $this->topNavigationAllowed = $topNavigationAllowed;
        $this->topNavigationByUserActivationAllowed = $topNavigationByUserActivationAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isFormAllowed(): bool
    {
        return $this->formAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isModalsAllowed(): bool
    {
        return $this->modalsAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isOrientationLockAllowed(): bool
    {
        return $this->orientationLockAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isPointerLockAllowed(): bool
    {
        return $this->pointerLockAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isPopupsAllowed(): bool
    {
        return $this->popupsAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isPopupsToEscapeSandboxAllowed(): bool
    {
        return $this->popupsToEscapeSandboxAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isPresentationAllowed(): bool
    {
        return $this->presentationAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isSameOriginAllowed(): bool
    {
        return $this->sameOriginAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isScriptsAllowed(): bool
    {
        return $this->scriptsAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isTopNavigationAllowed(): bool
    {
        return $this->topNavigationAllowed;
    }

    /**
     * Sandbox option.
     *
     * @return bool
     */
    public function isTopNavigationByUserActivationAllowed(): bool
    {
        return $this->topNavigationByUserActivationAllowed;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'sandbox';
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getValue(): string
    {
        $allowed = [];

        if ($this->isFormAllowed()) {
            $allowed[] = 'allow-forms';
        }
        if ($this->isModalsAllowed()) {
            $allowed[] = 'allow-modals';
        }
        if ($this->isOrientationLockAllowed()) {
            $allowed[] = 'allow-orientation-lock';
        }
        if ($this->isPointerLockAllowed()) {
            $allowed[] = 'allow-pointer-lock';
        }
        if ($this->isPopupsAllowed()) {
            $allowed[] = 'allow-popups';
        }
        if ($this->isPopupsToEscapeSandboxAllowed()) {
            $allowed[] = 'allow-popups-to-escape-sandbox';
        }
        if ($this->isPresentationAllowed()) {
            $allowed[] = 'allow-presentation';
        }
        if ($this->isSameOriginAllowed()) {
            $allowed[] = 'allow-same-origin';
        }
        if ($this->isScriptsAllowed()) {
            $allowed[] = 'allow-scripts';
        }
        if ($this->isTopNavigationAllowed()) {
            $allowed[] = 'allow-top-navigation';
        }
        if ($this->isTopNavigationByUserActivationAllowed()) {
            $allowed[] = 'allow-top-navigation-by-user-activation';
        }

        if (!$allowed) {
            throw new \RuntimeException('At least 1 option must be selected');
        }
        return implode(' ', $allowed);
    }
}
