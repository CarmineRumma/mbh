<?php

namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\PackageBundle\Document\PackageService;

/**
 * Class PackageServiceGroup

 */
class PackageServiceGroup// extends PackageService
{
    /**
     * @var PackageService[]
     */
    protected $group = [];

    public function add(PackageService $packageService)
    {
        $this->group[] = $packageService;
    }

    public function getTotal()
    {
        $total = 0;
        foreach($this->group as $packageService) {
            $total += $packageService->getTotal();
        }
        return $total;
    }

    public function getPrice()
    {
        $price = 0;
        foreach($this->group as $packageService) {
            $price += $packageService->getPrice();
        }
        return $price;
    }

    public function getActuallyAmount()
    {
        $actuallyAmount = 0;
        foreach($this->group as $packageService) {
            $actuallyAmount += $packageService->getActuallyAmount();
        }
        return $actuallyAmount;
    }

    public function getAmount()
    {
        $amount = 0;
        foreach($this->group as $packageService) {
            $amount += $packageService->getAmount();
        }
        return $amount;
    }

    /**
     * @return \MBH\Bundle\PackageBundle\Document\PackageService[]
     */
    public function getGroup()
    {
        return $this->group;
    }

}