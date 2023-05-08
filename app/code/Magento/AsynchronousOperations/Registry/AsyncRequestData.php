<?php declare(strict_types=1);

namespace Magento\AsynchronousOperations\Registry;

class AsyncRequestData
{
    /**
     * @var bool
     */
    private bool $isAuthorized = false;

    /**
     * Set async request authorized
     *
     * @param bool $isAuthorized
     * @return void
     */
    public function setAuthorized(bool $isAuthorized): void
    {
        $this->isAuthorized = $isAuthorized;
    }

    /**
     * Get async request authorized
     *
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->isAuthorized;
    }
}
