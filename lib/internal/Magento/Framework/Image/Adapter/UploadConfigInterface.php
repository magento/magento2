<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
namespace Magento\Framework\Image\Adapter;

/**
 * Interface UploadConfigInterface
 */
interface UploadConfigInterface
{
    /**
<<<<<<< HEAD
     * Get maximum image width.
     *
     * @return int
     */
    public function getMaxWidth(): int;

    /**
     * Get maximum image height.
     *
     * @return int
     */
    public function getMaxHeight(): int;
=======
     * @return int
     */
    public function getMaxWidth();

    /**
     * @return int
     */
    public function getMaxHeight();
>>>>>>> upstream/2.2-develop
}
