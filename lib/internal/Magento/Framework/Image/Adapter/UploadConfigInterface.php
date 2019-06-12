<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
namespace Magento\Framework\Image\Adapter;

/**
 * Interface UploadConfigInterface
<<<<<<< HEAD
=======
 * @deprecated moved to proper namespace and extended
 * @see \Magento\Backend\Model\Image\UploadResizeConfigInterface;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
interface UploadConfigInterface
{
    /**
<<<<<<< HEAD
     * @return int
     */
    public function getMaxWidth();

    /**
     * @return int
     */
    public function getMaxHeight();
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
}
