<?php

namespace MBH\Bundle\PackageBundle\Services;

use Gedmo\Loggable\Document\LogEntry;
use Gedmo\Loggable\Document\Repository\LogEntryRepository;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\CashBundle\Document\CashDocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Service\RoomTypeManager;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\OrderRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackagePrice;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\RoomCacheOverwrite;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  Calculation service
 */
class Calculation
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var RoomTypeManager
     */
    private $manager;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    private $helper;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->manager = $container->get('mbh.hotel.room_type_manager');
        $this->mergingTariffs = $this->dm->getRepository('MBHPriceBundle:Tariff')->getMergingTariffs();
        $this->helper = $container->get('mbh.helper');
    }

    /**
     * @param Order $order
     * @param CashDocument $newDoc
     * @param CashDocument $removeDoc
     * @return Order
     */
    public function setPaid(Order $order, CashDocument $newDoc = null, CashDocument $removeDoc = null)
    {
        $total = 0;
        $ids = [];

        if (!$this->dm->getFilterCollection()->isEnabled('softdeleteable')) {
            $this->dm->getFilterCollection()->enable('softdeleteable');
        }
        $cashes = $order->getCashDocuments();

        if ($newDoc) {
            $cashes[] = $newDoc;
        }
        foreach ($cashes as $cash) {
            if (!$cash->getIsPaid() || in_array($cash->getId(), $ids)) {
                continue;
            }
            $ids[] = $cash->getId();

            if ($removeDoc && $removeDoc->getId() == $cash->getId()) {
                continue;
            }
            if ($cash->getOperation() == 'out') {
                $total -= $cash->getTotal();
            } elseif ($cash->getOperation() == 'in') {
                $total += $cash->getTotal();
            }
        }

        $order->setPaid($total);
        $order->checkPaid();

        return $order;
    }

    public function setServicesPrice(Package $package, PackageService $newDoc = null, PackageService $removeDoc = null)
    {
        $total = 0;

        $services = $package->getServices();
        if ($services instanceof \Traversable) {
            $services = iterator_to_array($services);
        }
        if ($newDoc) {
            $services[] = $newDoc;
        }
        foreach ($services as $service) {
            if (!empty($service->getDeletedAt())) {
                continue;
            }
            if ($removeDoc && $removeDoc->getId() == $service->getId()) {
                continue;
            }
            $total += $service->getTotal();
        }

        $package->setServicesPrice($total);

        return $package;
    }


    /**
     * @param RoomType $roomType
     * @param Tariff $tariff
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param int $adults
     * @param int $children
     * @param Promotion|null $promotion
     * @param bool $useCategories
     * @param bool $useDuration
     * @param Special|null $special
     * @return array|bool
     */
    public function calcPrices(
        RoomType $roomType,
        Tariff $tariff,
        \DateTime $begin,
        \DateTime $end,
        $adults = 0,
        $children = 0,
        Promotion $promotion = null,
        $useCategories = false,
        Special $special = null,
        $useDuration = true
    )
    {
        $originTariff = $tariff;
        $prices = [];
        $memcached = $this->container->get('mbh.cache');
        $places = $roomType->getPlaces();
        $hotel = $roomType->getHotel();
        $useCategories ? $isChildPrices = $roomType->getCategory()->getIsChildPrices() : $isChildPrices = $roomType->getIsChildPrices();
        $useCategories ? $isIndividualAdditionalPrices = $roomType->getCategory()->getIsIndividualAdditionalPrices() : $isIndividualAdditionalPrices = $roomType->getIsIndividualAdditionalPrices();
        $endPlus = clone $end;
        $endPlus->modify('+1 day');

        if ($this->manager->useCategories) {
            if (!$roomType->getCategory()) {
                return false;
            }
            $roomTypeId = $roomType->getCategory()->getId();
        } else {
            $roomTypeId = $roomType->getId();
        }
        if ($tariff->getParent() && $tariff->getChildOptions()->isInheritPrices()) {
            $tariff = $tariff->getParent();
        }
        $tariffId = $tariff->getId();
        $duration = $end->diff($begin)->format('%a') + 1;
        $priceCachesCallback = function () use ($begin, $end, $hotel, $roomTypeId, $tariffId, $memcached) {
            return $this->dm->getRepository('MBHPriceBundle:PriceCache')
                ->fetch($begin, $end, $hotel, [$roomTypeId], [$tariffId], true, $this->manager->useCategories, $memcached);
        };

        $priceCaches = $this->helper->getFilteredResult($this->dm, $priceCachesCallback);

        if (!$tariff->getIsDefault()) {
            $defaultTariff = $this->dm->getRepository('MBHPriceBundle:Tariff')->fetchBaseTariff($hotel, null, $memcached);
            if (!$defaultTariff) {
                return false;
            }
            $defaultPriceCachesCallback = function () use ($begin, $end, $hotel, $roomTypeId, $defaultTariff, $memcached) {
                return $this->dm->getRepository('MBHPriceBundle:PriceCache')
                    ->fetch($begin, $end, $hotel, [$roomTypeId], [$defaultTariff->getId()], true, $this->manager->useCategories, $memcached);
            };
            $defaultPriceCaches = $this->helper->getFilteredResult($this->dm, $defaultPriceCachesCallback);
        } else {
            $defaultPriceCaches = $priceCaches;
            $defaultTariff = $tariff;
        }


        $mergingTariffsPrices = [];
        foreach ($this->mergingTariffs as $mergingTariff) {
            if ($mergingTariff->getParent() && $mergingTariff->getChildOptions()->isInheritPrices()) {
                $ids = [$mergingTariff->getParent()->getId()];
            } else {
                $ids = [$mergingTariff->getId()];
            }

            $mergingTariffCallback = function () use ($begin, $end, $hotel, $roomTypeId, $ids, $memcached) {
                return $this->dm->getRepository('MBHPriceBundle:PriceCache')->fetch(
                    $begin,
                    $end,
                    $hotel,
                    [$roomTypeId],
                    $ids,
                    true,
                    $this->manager->useCategories,
                    $memcached
                );
            };
            $mergingTariff = $this->helper->getFilteredResult($this->dm, $mergingTariffCallback);

            if ($mergingTariff) {
                $mergingTariffsPrices += $mergingTariff;
            }
        }

        if (!isset($priceCaches[$roomTypeId][$tariffId])) {
            return false;
        }

        $caches = [];
        foreach (new \DatePeriod($begin, \DateInterval::createFromDateString('1 day'), $endPlus) as $cacheDay) {
            $cacheDayStr = $cacheDay->format('d.m.Y');

            if (isset($priceCaches[$roomTypeId][$tariffId][$cacheDayStr])) {
                $caches[$cacheDayStr] = $priceCaches[$roomTypeId][$tariffId][$cacheDayStr];
            } else {
                foreach ($this->mergingTariffs as $mergingTariff) {
                    if (isset($mergingTariffsPrices[$roomTypeId][$mergingTariff->getId()][$cacheDayStr])) {
                        $caches[$cacheDayStr] = $mergingTariffsPrices[$roomTypeId][$mergingTariff->getId()][$cacheDayStr];
                        break;
                    }
                }
            }

            if (empty($caches[$cacheDayStr]) && isset($defaultPriceCaches[$roomTypeId][$defaultTariff->getId()][$cacheDayStr])) {
                $caches[$cacheDayStr] = $defaultPriceCaches[$roomTypeId][$defaultTariff->getId()][$cacheDayStr];
            }
        }

        if ($useDuration && (count($caches) != $duration)) {
            return false;
        }

        //places
        if ($adults == 0 & $children == 0) {
            $combinations = $roomType->getAdultsChildrenCombinations($useCategories);
        } else {
            $combinations = [0 => ['adults' => $adults, 'children' => $children]];
        }

        foreach ($combinations as $combination) {
            $total = 0;
            $dayPrices = $packagePrices = [];
            foreach ($caches as $day => $cache) {
                $promoConditions = PromotionConditionFactory::checkConditions(
                    $promotion,
                    $duration,
                    $combination['adults'],
                    $combination['children']
                );

                if ($cache->getTariff()->getId() != $tariff->getId()) {
                    $promoConditions = false;
                }

                $totalChildren = $combination['children'];
                $totalAdults = $combination['adults'];

                if ($promoConditions) {
                    $totalChildren -= (int)$promotion->getFreeChildrenQuantity();
                    $totalAdults -= (int)$promotion->getFreeAdultsQuantity();
                    $totalAdults = $totalAdults >= 1 ? $totalAdults : 1;
                    $totalChildren = $totalChildren >= 0 ? $totalChildren : 0;
                    $childrenDiscount = $promotion->getChildrenDiscount();
                }

                $all = $totalAdults + $totalChildren;
                $adds = $all - $places;

                if ($all > $places) {
                    if ($totalAdults >= $places) {
                        $mainAdults = $places;
                        $mainChildren = 0;
                    } else {
                        $mainAdults = $totalAdults;
                        $mainChildren = $places - $totalAdults;
                    }

                    if ($adds > $totalChildren) {
                        $addsChildren = $totalChildren;
                        $addsAdults = $adds - $addsChildren;
                    } else {
                        $addsChildren = $adds;
                        $addsAdults = 0;
                    }
                } else {
                    $mainAdults = $totalAdults;
                    $mainChildren = $totalChildren;
                    $addsAdults = 0;
                    $addsChildren = 0;
                }

                $dayPrice = 0;

                if ($cache->getSinglePrice() !== null &&
                    $all == 1 &&
                    !$cache->getCategoryOrRoomType($this->manager->useCategories)->getIsHostel()
                ) {
                    $dayPrice += $cache->getSinglePrice();
                } elseif ($cache->getIsPersonPrice()) {
                    if ($isChildPrices && $cache->getChildPrice() !== null) {
                        $childrenPrice = $mainChildren * $cache->getChildPrice();
                    } else {
                        $childrenPrice = $mainChildren * $cache->getPrice();
                    }
                    if ($promoConditions && $childrenDiscount) {
                        $childrenPrice = $childrenPrice * (100 - $childrenDiscount) / 100;
                    }
                    $dayPrice += $mainAdults * $cache->getPrice() + $childrenPrice;
                } else {
                    $dayPrice += $cache->getPrice();
                }

                //calc adds
                if ($addsChildren && $cache->getAdditionalChildrenPrice() === null) {
                    continue 2;
                }
                if ($addsAdults && $cache->getAdditionalPrice() === null) {
                    continue 2;
                }

                if ($isIndividualAdditionalPrices and ($addsChildren + $addsAdults) > 1) {
                    $addsPrice = 0;
                    $additionalCalc = function ($num, $prices, $price, $offset = 0) {
                        $result = 0;
                        for ($i = 0; $i < $num; $i++) {
                            if (isset($prices[$i + $offset]) && $prices[$i + $offset] !== null) {
                                $result += $prices[$i + $offset];
                            } else {
                                $result += $price;
                            }
                        }

                        return $result;
                    };

                    $addsPrice += $additionalCalc($addsAdults, $cache->getAdditionalPrices(), $cache->getAdditionalPrice());
                    $addsChildrenPrice = $additionalCalc($addsChildren, $cache->getAdditionalChildrenPrices(), $cache->getAdditionalChildrenPrice(), $addsAdults);

                    if ($promoConditions && $childrenDiscount) {
                        $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
                    }
                    $addsPrice += $addsChildrenPrice;
                } else {
                    $addsChildrenPrice = $addsChildren * $cache->getAdditionalChildrenPrice();

                    if ($promoConditions && $childrenDiscount) {
                        $addsChildrenPrice = $addsChildrenPrice * (100 - $childrenDiscount) / 100;
                    }

                    $addsPrice = $addsAdults * $cache->getAdditionalPrice() + $addsChildrenPrice;
                }

                $dayPrice += $addsPrice;

                // calc promotion discount
                if ($promoConditions) {
                    $dayPrice -= PromotionConditionFactory::calcDiscount($promotion, $dayPrice, true);
                }

                $packagePrice = $this->getPackagePrice($dayPrice, $cache->getDate(), $originTariff, $roomType, $special);
                $dayPrice = $packagePrice->getPrice();
                $dayPrices[str_replace('.', '_', $day)] = $dayPrice;

                if ($promoConditions) {
                    $packagePrice->setPromotion($promotion);
                }
                $packagePrices[] = $packagePrice;
                $total += $dayPrice;
            }

            $promoConditions = PromotionConditionFactory::checkConditions(
                $promotion,
                $duration,
                $combination['adults'],
                $combination['children']
            );

            if ($promoConditions) {
                $total -= PromotionConditionFactory::calcDiscount($promotion, $total, false);
            }

            $prices[$combination['adults'] . '_' . $combination['children']] = [
                'adults' => $combination['adults'],
                'children' => $combination['children'],
                'total' => $this->getTotalPrice($total),
                'prices' => $dayPrices,
                'packagePrices' => $packagePrices,
            ];
        }

        return $prices;
    }

    /**
     * Returns raw total price or rounded if method overriden.
     * @param $total
     * @return mixed
     */
    protected function getTotalPrice($total)
    {
        return $total;
    }

    /**
     * @param $price
     * @param \DateTime $date
     * @param Tariff $tariff
     * @param RoomType $roomType
     * @param Special|null $special
     * @return PackagePrice
     */
    public function getPackagePrice($price, \DateTime $date, Tariff $tariff, RoomType $roomType, Special $special = null): PackagePrice
    {
        $packagePrice = new PackagePrice($date, $price > 0 ? $price : 0, $tariff);
        if ($special &&
            $date >= $special->getBegin() && $date <= $special->getEnd() &&
            $special->check($roomType) && $special->check($tariff)
        ) {
            $price = $special->isIsPercent() ? $price - $price * $special->getDiscount() / 100 : $price - $special->getDiscount();
            $packagePrice->setPrice($price)->setSpecial($special);
        }
        return $packagePrice;
    }

    /**
     * @param \DateTime $begin
     * @param Hotel[] $hotels
     */
    public function getDebts(\DateTime $begin, array $hotels = [])
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->dm->getRepository('MBHPackageBundle:Order');
        $qb = $orderRepository->createQueryBuilder();
        $qb
            ->addOr($qb->expr()
                ->field('updatedAt')->lt($begin)
                ->where('function() {
                return this.price != this.paid;
            }'))
            ->addOr($qb->expr()
                ->field('updatedAt')->gte($begin)
            );

        /** @var Order[] $orders */
        $orders = $qb
            ->getQuery()
            ->execute()
            ->toArray();
        $ordersByIds = [];
        foreach ($orders as $order) {
            $ordersByIds[$order->getId()] = $order;
        }

        $ordersIds = array_keys($ordersByIds);

        /** @var CashDocument[] $cashDocs */
        $cashDocs = $this->dm
            ->getRepository('MBHCashBundle:CashDocument')
            ->createQueryBuilder()
            ->field('order.id')->in($ordersIds)
//            ->field('paidDate')->g
            ->getQuery()
            ->execute();

        $cashDocsByOrderId = [];
        foreach ($cashDocs as $cashDoc) {
            $cashDocsByOrderId[$cashDoc->getOrder()->getId()][] = $cashDoc;
        }

        /** @var Package[] $packages */
        $packages = $this->dm
            ->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('order.id')->in($ordersIds)
            ->getQuery()
            ->execute();

        $packagesByOrderIds = [];
        foreach ($packages as $package) {
            $packagesByOrderIds[$package->getOrder()->getId()][] = $package;
        }

        $kreditDebtCash = [];
        $kreditDebtCashless = [];
        $debitPartlyPaid = [];
        $debitNotPaid = [];
        foreach ($ordersByIds as $orderId => $order) {
            /** @var CashDocument[] $cashDocuments */
            $cashDocuments = isset($cashDocsByOrderId[$orderId]) ? $cashDocsByOrderId[$orderId] : [];
            $packages = isset($packagesByOrderIds[$orderId]) ? $packagesByOrderIds[$orderId] : [];

            $hotelsPriceFractions = [];
            /** @var Package $package */
            foreach ($packages as $package) {
                if (empty($package->getDeletedAt()) || $package->getDeletedAt() > $begin) {
                    $hotelId = $package->getHotel()->getId();
                    $fraction = $package->getPrice() / $package->getOrder()->getPrice();
                    isset($hotelsPriceFractions[$hotelId])
                        ? $hotelsPriceFractions[$hotelId] += $fraction
                        : $hotelsPriceFractions[$hotelId] = $fraction;
                }
            }

            $cashIncoming = 0;
            $cashlessIncoming = 0;
            $refunds = 0;
            foreach ($cashDocuments as $cashDocument) {
                if ($cashDocument->getOperation() == 'in' || $cashDocument->getOperation() == 'fee') {
                    $cashDocument->getMethod() == 'cash'
                        ? $cashIncoming += $cashDocument->getTotal()
                        : $cashlessIncoming += $cashDocument->getTotal();
                } elseif ($cashDocument->getOperation() == 'out') {
                    $refunds -= $cashDocument->getTotal();
                }
            }

            $incomingSum = $cashIncoming + $cashlessIncoming;
            $cashIncomingFraction = $cashIncoming / $incomingSum;

            if ($incomingSum == 0) {
                foreach ($hotelsPriceFractions as $hotelId => $priceFraction) {
                    $packageHotelPrice = $incomingSum * $priceFraction;
                    isset($debitNotPaid[$hotelId])
                        ? $debitNotPaid[$hotelId] += $packageHotelPrice
                        : $debitNotPaid[$hotelId] = $packageHotelPrice;
                }
            } elseif ($incomingSum) {

            }
        }

        return [
            $kreditDebtCash,
            $kreditDebtCashless,
            $debitNotPaid,
            $debitPartlyPaid
        ];
    }

    /**
     * @param Package[] $packages
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param bool $asCalculatedPrice
     * @return array
     */
    public function calcDailyPrices($packages, \DateTime $begin, \DateTime $end, $asCalculatedPrice  = true)
    {
        $earliestCreationDate = null;
        foreach ($packages as $package) {
            if (is_null($earliestCreationDate) || $package->getCreatedAt() < $earliestCreationDate) {
                $earliestCreationDate = $package->getCreatedAt();
            }
        }
        /** @var LogEntryRepository $logEntryRepo */
        $logEntryRepo = $this->dm->getRepository('GedmoLoggable:LogEntry');
        $packageIds = $this->helper->toIds($packages);
        /** @var LogEntry[] $logs */
        $logs = $logEntryRepo
            ->createQueryBuilder()
            ->field('objectId')->in($packageIds)
            ->field('objectClass')->equals('MBH\Bundle\PackageBundle\Document\Package')
            ->field('loggedAt')->gte($earliestCreationDate)
            ->field('loggedAt')->lte($end)
            ->sort('loggedAt')
            ->getQuery()
            ->execute()
            ->toArray();

        $sortedLogData = [];
        foreach ($logs as $log) {
            $dateString = $log->getLoggedAt()->format('d.m.Y');
            $logData = $log->getData();
            !isset($logData['price']) ?: $sortedLogData[$log->getObjectId()][$dateString]['price'] = $logData['price'];
            !isset($logData['totalOverwrite']) ?: $sortedLogData[$log->getObjectId()][$dateString]['totalOverwrite'] = $logData['totalOverwrite'];
            !isset($logData['servicesPrice']) ?: $sortedLogData[$log->getObjectId()][$dateString]['servicesPrice'] = $logData['servicesPrice'];
            !isset($logData['isPercentDiscount']) ?: $sortedLogData[$log->getObjectId()][$dateString]['isPercentDiscount'] = $logData['isPercentDiscount'];
            !isset($logData['discount']) ?: $sortedLogData[$log->getObjectId()][$dateString]['discount'] = $logData['discount'];
        }

        $prices = [];
        foreach ($packages as $package) {
            $beginDate = $begin < $package->getCreatedAt() ? $package->getCreatedAt() : $begin;
            /** @var \DateTime $date */
            foreach (new \DatePeriod($beginDate, new \DateInterval('P1D'), $end) as $date) {
                $dateString = $date->format('d.m.Y');
                $packageId = $package->getId();
                $price = isset($sortedLogData[$packageId][$dateString]['price'])
                    ? $sortedLogData[$packageId][$dateString]['price']
                    : $package->getPackagePrice();
                $totalOverwrite = isset($sortedLogData[$packageId][$dateString]['totalOverwrite'])
                    ? $sortedLogData[$packageId][$dateString]['totalOverwrite']
                    : $package->getTotalOverwrite();
                $servicesPrice = isset($sortedLogData[$packageId][$dateString]['servicesPrice'])
                    ? $sortedLogData[$packageId][$dateString]['servicesPrice']
                    : $package->getServicesPrice();
                $isPercentDiscount = isset($sortedLogData[$packageId][$dateString]['isPercentDiscount'])
                    ? $sortedLogData[$packageId][$dateString]['isPercentDiscount']
                    : $package->getIsPercentDiscount();
                $discount = isset($sortedLogData[$packageId][$dateString]['discount'])
                    ? $sortedLogData[$packageId][$dateString]['discount']
                    : $package->getDiscount();

                if ($asCalculatedPrice) {
                    $prices[$packageId][$dateString] =
                }
                $prices[$packageId][$dateString] = [
                    'price' => $price,
                    'totalOverWrite' => $totalOverwrite,
                    'servicesPrice' => $servicesPrice,
                    'isPercentDiscount' => $isPercentDiscount,
                    'discount' => $discount
                ];
            }
        }

        return $prices;
    }
}
