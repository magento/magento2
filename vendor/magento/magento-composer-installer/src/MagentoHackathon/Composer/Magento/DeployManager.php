<?php
/**
 *
 *
 *
 *
 */

namespace MagentoHackathon\Composer\Magento;


use Composer\IO\IOInterface;
use MagentoHackathon\Composer\Magento\Deploy\Manager\Entry;
use MagentoHackathon\Composer\Magento\Deploystrategy\Copy;

class DeployManager
{

    /**
     * @var Entry[]
     */
    protected $packages = array();

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * an array with package names as key and priorities as value
     *
     * @var array
     */
    protected $sortPriority = array();

    public function __construct(IOInterface $io)
    {
        $this->io = $io;
    }


    public function addPackage(Entry $package)
    {
        $this->packages[] = $package;
    }

    public function setSortPriority($priorities)
    {
        $this->sortPriority = $priorities;
    }

    /**
     * uses the sortPriority Array to sort the packages.
     * Highest priority first.
     * Copy gets per default higher priority then others
     */
    protected function sortPackages()
    {
        $sortPriority = $this->sortPriority;
        $getPriorityValue = function (Entry $object) use ($sortPriority) {
            $result = 100;
            if (isset($sortPriority[$object->getPackageName()])) {
                $result = $sortPriority[$object->getPackageName()];
            } elseif ($object->getDeployStrategy() instanceof Copy) {
                $result = 101;
            }
            return $result;
        };
        usort(
            $this->packages,
            function ($a, $b) use ($getPriorityValue) {
                /** @var Entry $a */
                /** @var Entry $b */
                $aVal = $getPriorityValue($a);
                $bVal = $getPriorityValue($b);
                if ($aVal == $bVal) {
                    return 0;
                }
                return ($aVal > $bVal) ? -1 : 1;
            }
        );
    }

    public function doDeploy()
    {
        $this->sortPackages();

        /** @var Entry $package */
        foreach ($this->packages as $package) {
            if ($this->io->isDebug()) {
                $this->io->write('start magento deploy for ' . $package->getPackageName());
            }
            try {
                $package->getDeployStrategy()->deploy();
            } catch (\ErrorException $e) {
                if ($this->io->isDebug()) {
                    $this->io->write($e->getMessage());
                }
            }

        }
    }
}
