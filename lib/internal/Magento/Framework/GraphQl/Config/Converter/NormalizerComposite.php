<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter;

/**
 * {@inheritdoc}
 */
class NormalizerComposite implements NormalizerInterface
{
    /**
     * @var NormalizerInterface[]
     */
    private $normalizers;

    /**
     * @param NormalizerInterface[] $normalizers
     */
    public function __construct(array $normalizers)
    {
        $this->normalizers = $normalizers;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize(array $source) : array
    {
        $normalizedResult = [];
        foreach ($this->normalizers as $normalizer) {
            $normalizedResult = array_merge($normalizedResult, $normalizer->normalize($source));
        }

        return $normalizedResult;
    }
}
