<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\ClaimCheckerManager as Manager;
use Jose\Component\Checker\ClaimCheckerManagerFactory;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Checker\MissingMandatoryClaimException;
use Magento\Framework\ObjectManager\TMapFactory;

/**
 * Checks claims from payload. The list of claim checkers can be provided via di.xml.
 *
 * Each claim checker should implement \Magento\Framework\Jwt\ClaimCheckerInterface.
 */
class ClaimCheckerManager
{
    /**
     * @var array
     */
    private $checkers;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var ClaimCheckerManagerFactory
     */
    private $checkerManagerFactory;

    /**
     * @var TMapFactory
     */
    private $mapFactory;

    /**
     * @var array
     */
    private $mandatoryClaims = [];

    /**
     * @param TMapFactory $mapFactory
     * @param ClaimCheckerManagerFactory $checkerManagerFactory
     * @param array $checkers
     * @param array $mandatoryClaims
     */
    public function __construct(
        TMapFactory $mapFactory,
        ClaimCheckerManagerFactory $checkerManagerFactory,
        array $checkers = [],
        array $mandatoryClaims = []
    ) {
        $this->mapFactory = $mapFactory;
        $this->checkerManagerFactory = $checkerManagerFactory;
        $this->checkers = $checkers;
        $this->mandatoryClaims = $mandatoryClaims;
    }

    /**
     * Checks claims. All claims are checked against the claim checkers.
     *
     * If one fails, the \InvalidArgumentException is thrown.
     *
     * @param array $claims
     * @return array
     * @throws \InvalidArgumentException
     */
    public function check(array $claims): array
    {
        $manager = $this->getManager();
        if (empty($manager->getCheckers())) {
            return [];
        }

        try {
            $converted = $this->convertMultiToSingle($claims);
            return $manager->check($converted, $this->mandatoryClaims);
        } catch (InvalidClaimException | MissingMandatoryClaimException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Initializes claim checker manager.
     *
     * @return Manager
     */
    private function getManager(): Manager
    {
        if ($this->manager === null) {
            $aliases = [];
            $checkers = $this->mapFactory->create(
                [
                    'array' => $this->checkers,
                    'type' => ClaimChecker::class,
                ]
            );

            /** @var ClaimChecker $checker */
            foreach ($checkers as $checker) {
                $this->checkerManagerFactory->add($checker->supportedClaim(), $checker);
                $aliases[] = $checker->supportedClaim();
            }

            $this->manager = $this->checkerManagerFactory->create($aliases);
        }

        return $this->manager;
    }

    /**
     * To support native claims validation the multidimensional array should be converted to one-dimensional.
     *
     * @param array $original
     * @return array
     */
    private function convertMultiToSingle(array $original): array
    {
        $result = [];
        foreach ($original as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->convertMultiToSingle($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
