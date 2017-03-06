<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Lib\TranslatableInterface;
use MBH\Bundle\BaseBundle\Service\Currency;
use MBH\Bundle\CashBundle\Document\CardType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorConfig;
use MBH\Bundle\ChannelManagerBundle\Document\TripAdvisorTariff;
use MBH\Bundle\ChannelManagerBundle\Services\OrderHandler;
use MBH\Bundle\HotelBundle\Document\ContactInfo;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeImage;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\SearchResult;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffService;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class TripAdvisorResponseFormatter
{
    const API_VERSION = 7;
    const TRIP_ADVISOR_DATE_FORMAT = 'Y-m-d';
    const TRIP_ADVISOR_AVAILABLE_CARD_TYPES = ['Visa', 'MasterCard', 'AmericanExpress', 'Discover'];
    const ROOM_NOT_AVAILABLE_ERROR = 'RoomNotAvailable';
    const PRICE_MISMATCH = 'PriceMismatch';
    const MISSING_EMAIL = 'MissingEmail';
    const MISSING_PAYER_FIRST_NAME = 'MissingTravelerFirstName';

    const RATE_MEAL_TYPES = [
        'All Inclusive' => 1,
        'Buffet breakfast' => 4,
        'Continental breakfast' => 6,
        'Full english breakfast' => 7,
        'Full board' => 10,
        'Half board' => 12,
        'Breakfast' => 19,
        'Lunch' => 21,
        'Dinner' => 22,
        'Breakfast and Lunch' => 23
    ];

    const RATE_AMENITIES = [
        'bar' => 69,
        'bath' => 85,
        'toilets' => 120,
        'wifi' => 900128
    ];

    const HOTEL_AMENITIES = [
        'bath' => 'BATHROOMS',
        'apartments' => 'APARTMENTS',
        'beach' => 'BEACH',
        'credit-card' => 'CREDIT_CARDS_ACCEPTED',
        'fitness' => 'FITNESS_CENTER',
        'breakfast' => 'FREE_BREAKFAST',
        'parking' => 'PARKING_AVAILABLE',
        'free-wifi' => 'FREE_WIFI',
        'luxury_holidays' => 'LUXURY',
        'swimming' => 'SWIMMING_POOL'
    ];

    const ROOM_AMENITIES = [
        'conditioner' => 2,
        'radio' => 5,
        'double-bed' => 33,
        'internet' => 54,
        'iron' => 55,
        'kitchen' => 59,
        'bar' => 69,
        'bath' => 85,
        'fridge' => 88,
        'telephone' => 107,
        'toilets' => 120,
        'shower' => 142,
        'free-wifi' => 900126,
        'wifi' => 900128
    ];

    private $confirmationPage;
    private $domainName;
    private $arrivalTime;
    private $departureTime;
    private $onlineFormUrl;
    private $locale;
    /** @var  DocumentManager $dm */
    private $dm;
    /** @var  UploaderHelper */
    private $uploaderHelper;
    /** @var  Currency $currencyHandler */
    private $currencyHandler;
    private $localCurrency;
    /** @var  OrderHandler $channelManagerHelper */
    private $orderHandler;

    public function __construct(
        $confirmationPageUrl,
        $domainName,
        $arrivalTime,
        $departureTime,
        $onlineFormUrl,
        $locale,
        DocumentManager $dm,
        UploaderHelper $uploaderHelper,
        Currency $currencyHandler,
        $localCurrency,
        OrderHandler $orderHandler
    ) {
        $this->confirmationPage = $confirmationPageUrl;
        $this->domainName = $domainName;
        $this->arrivalTime = $arrivalTime;
        $this->departureTime = $departureTime;
        $this->onlineFormUrl = $onlineFormUrl;
        $this->locale = $locale;
        $this->dm = $dm;
        $this->uploaderHelper = $uploaderHelper;
        $this->currencyHandler = $currencyHandler;
        $this->localCurrency = $localCurrency;
        $this->orderHandler = $orderHandler;
    }

    public function formatConfigResponse(Hotel $hotel)
    {
        $response = [];
        $response['api_version'] = TripAdvisorResponseFormatter::API_VERSION;

        $configurationData = [];

        $hotelContactInformation = $hotel->getContactInformation();
        $configurationData['emergency_contacts'][] = $this->getContactInfo($hotelContactInformation);

        $configurationData['info_contacts'][] = $this->getContactInfo($hotelContactInformation);

        $configurationData['languages'] = $hotel->getSupportedLanguages();
        //pref_hotels(предпочитаемое кол-во отелей за запрос)
        // и five_min_rate_limit(предпочитаемое кол-во запросов за 5 минут)

        $response['configuration'] = $configurationData;

        return $response;
    }

    public function formatHotelInventoryData($apiVersion, $language, $configs)
    {
        $hotelsData = [];
        /** @var TripAdvisorConfig $config */
        foreach ($configs as $config) {
            $hotel = $config->getHotel();
            $contactInformation = $hotel->getContactInformation();
            /** @var Hotel $hotel */
            $hotelData = [
                'ta_id' => $config->getHotelId(),
                'partner_id' => $hotel->getId(),
                'name' => $hotel->getInternationalTitle(),
                'street' => $hotel->getInternationalStreetName(),
                'city' => $this->getTranslatableTitle($hotel->getCity()),
                'state' => $this->getTranslatableTitle($hotel->getRegion()),
                'country' => $this->getTranslatableTitle($hotel->getCountry()),
                'amenities' => $this->getAvailableHotelAmenities($hotel->getFacilities()),
                'url' => $config->getHotelUrl(),
                'room_types' => []
            ];
            if ($contactInformation->getPhoneNumber()) {
                $hotelData['phone'] = $contactInformation->getPhoneNumber();
            }
            if ($contactInformation->getEmail()) {
                $hotelData['email'] = $contactInformation->getEmail();
            }
            if ($hotel->getLatitude()) {
                $hotelData['latitude'] = $hotel->getLatitude();
            }
            if ($hotel->getLongitude()) {
                $hotelData['longitude'] = $hotel->getLongitude();
            }
            if ($hotel->getDescription()) {
                $hotelData['desc'] = $hotel->getDescription();
            }
            if ($hotel->getZipCode()) {
                $hotelData['postal_code'] = $hotel->getZipCode();
            }

            foreach ($hotel->getRoomTypes() as $roomType) {
                $tripAdvisorRoomType = $config->getTARoomTypeByMBHRoomTypeId($roomType->getId());
                if ($roomType->getDescription() && !is_null($tripAdvisorRoomType) && $tripAdvisorRoomType->getIsEnabled()) {
                    $roomTypeData = [];
                    if ($roomType->getDescription()) {
                        $roomTypeData['desc'] = $roomType->getDescription();
                    }
                    $hotelData['room_types'][] = $roomTypeData;
                }
            }
            $hotelsData[] = $hotelData;
        }

        $response = [
            'api_version' => $apiVersion,
            'lang' => $language,
            'hotels' => $hotelsData
        ];

        return $response;
    }

    public function formatHotelAvailability(
        $availabilityData,
        $apiVersion,
        $requestedHotels,
        $startDate,
        $endDate,
        $adultsChildrenCombinations,
        $language,
        $queryKey,
        $currency,
        $userCountry,
        $deviceType,
        $configs
    ) {
        $response = [
            'api_version' => $apiVersion,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'party' => $adultsChildrenCombinations,
            'query_key' => $queryKey,
            'lang' => $language
        ];

        $currency = $currency ?? mb_strtoupper($this->localCurrency);
        if (!is_null($currency)) {
            $response['currency'] = $currency;
        }
        if (!is_null($userCountry)) {
            $response['user_country'] = $userCountry;
        }
        if (!is_null($deviceType)) {
            $response['device_type'] = $deviceType;
        }

        $missingConfigsTAHotelIds = [];
        foreach ($requestedHotels as $requestedHotelData) {
            $taHotelId = $requestedHotelData['ta_id'];
            $response['hotel_ids'][] = $taHotelId;

            if (!isset($configs[$taHotelId])) {
                $missingConfigsTAHotelIds[] = $taHotelId;
            }
        }

        if (count($missingConfigsTAHotelIds) > 0) {
            $response['errors'][] = [
                "error_code" => 3,
                "hotel_ids" => $missingConfigsTAHotelIds
            ];
        }

        $hotelsAvailabilityData = [];
        foreach ($availabilityData as $ta_id => $hotelAvailabilityData) {
            $hotelAvailabilityResponseData = [];
            foreach ($hotelAvailabilityData as $roomTypeAvailabilityResults) {
                //Для каждого типа номера используется только 1 тариф, поэтому 1 результат
                /** @var SearchResult $roomTypeAvailabilityData */
                $roomTypeAvailabilityData = current($roomTypeAvailabilityResults['results']);
                $config = $roomTypeAvailabilityData->getRoomType()->getHotel()->getTripAdvisorConfig();
                $tripAdvisorRoomType =
                    $config->getTARoomTypeByMBHRoomTypeId($roomTypeAvailabilityData->getRoomType()->getId());

                if ($roomTypeAvailabilityData && !is_null($tripAdvisorRoomType) && $tripAdvisorRoomType->getIsEnabled()) {
                    $priceData = $this->getPriceDataByAdultsChildrenCombinations($adultsChildrenCombinations,
                        $roomTypeAvailabilityData);

                    if ($priceData != false) {
                        $begin = \DateTime::createFromFormat(self::TRIP_ADVISOR_DATE_FORMAT, $startDate);
                        $end = \DateTime::createFromFormat(self::TRIP_ADVISOR_DATE_FORMAT, $endDate);
                        $firstAdultsChildrenCombination = current($adultsChildrenCombinations);
                        $firstAdultsChildrenCounts = current($this->orderHandler->getAdultsChildrenCount(
                            [$firstAdultsChildrenCombination], $roomTypeAvailabilityData->getTariff()));
                        $locale = substr($language, 0, 2);
                        $url = $this->getSearchUrl($roomTypeAvailabilityData->getRoomType()->getId(), $begin, $end,
                            $firstAdultsChildrenCounts['adultsCount'], $firstAdultsChildrenCounts['childrenCount'],
                            $locale);

                        //TODO: Если не местные то что делать? Та же проблема, что и в homeaway
                        $price = $this->currencyHandler->convertFromRub($priceData['price'], $currency);
                        $roomTypeResponseData = [
                            'url' => $url,
                            'price' => $price,
                            //TODO: Мб и не так
                            'num_rooms' => count($adultsChildrenCombinations),
                            'fees' => 0,
                            'fees_at_checkout' => 0,
                            'taxes' => 0,
                            'taxes_at_checkout' => 0,
                            'final_price' => 0,
                            'currency' => $currency
                        ];

                        /** @var RoomType $roomType */
                        $roomType = $roomTypeAvailabilityResults['roomType'];
                        $amenities = $this->getAvailableHotelAmenities($roomType->getFacilities());
                        if (count($amenities) > 0) {
                            $roomTypeResponseData['room_amenities'] = $amenities;
                        }
                        $hotelAvailabilityResponseData['room_types'][$roomType->getName()] = $roomTypeResponseData;
                    }
                }
            }

            if (count($hotelAvailabilityResponseData) > 0) {
                $hotelAvailabilityResponseData['hotel_id'] = $ta_id;
                $hotelsAvailabilityData[] = $hotelAvailabilityResponseData;
            }
        }

        $response['num_hotels'] = count($hotelsAvailabilityData);
        $response['hotels'] = $hotelsAvailabilityData;

        return $response;
    }

    public function formatBookingAvailability(
        $availabilityData,
        Hotel $hotel,
        $apiVersion,
        $hotelData,
        $startDate,
        $endDate,
        $adultsChildrenCombination,
        $language,
        $queryKey,
        $userCountry,
        $deviceType,
        $currency
    ) {
        $response = [
            'api_version' => $apiVersion,
            'hotel_id' => $hotelData['ta_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'party' => $adultsChildrenCombination,
            'lang' => $language,
            'query_key' => $queryKey,
            'user_country' => $userCountry,
            'hotel_rate_plans' => [],
            'hotel_room_types' => [],
            'hotel_room_rates' => [],
            'hotel_details' => $this->getHotelDetails($hotel, $language),
            'accepted_credit_cards' => $this->getAcceptedCardTypes($hotel),
            'terms_and_conditions' => $hotel->getTripAdvisorConfig()->getTermsAndConditions(),
            'payment_policy' => $hotel->getTripAdvisorConfig()->getPaymentPolicy(),
            'customer_support' => $this->getContactInfo($hotel->getContactInformation())
        ];

        if ($deviceType) {
            $response['device_type'] = $deviceType;
        }

        $config = $hotel->getTripAdvisorConfig();
        if (is_null($config) || !$config->getIsEnabled()) {
            $response['errors'][] = [
                "error_code" => 3,
                "hotel_ids" => [$hotelData['ta_id']]
            ];
        } else {
            foreach ($availabilityData as $roomTypeAvailabilityData) {
                /** @var RoomType $roomType */
                $roomType = $roomTypeAvailabilityData['roomType'];
                foreach ($roomTypeAvailabilityData['results'] as $searchResult) {
                    /** @var SearchResult $searchResult */
                    $tariff = $searchResult->getTariff();
                    $tripAdvisorTariff = $config->getTATariffByMBHTariffId($tariff->getId());
                    if (!is_null($tripAdvisorTariff) && $tripAdvisorTariff->getIsEnabled()) {
                        if (!isset($response['hotel_room_types'][$roomType->getId()])) {
                            $response['hotel_room_types'][$roomType->getId()] = $this->getRoomTypeData($roomType,
                                $language);
                        }
                        if (!isset($response['hotel_rate_plans'][$tariff->getId()])) {
                            $response['hotel_rate_plans'][$tariff->getId()] = $this->getTariffData($tripAdvisorTariff);
                        }
                        $hotelRoomRate = $this->getHotelRoomRates($searchResult, $adultsChildrenCombination, $currency,
                            $config, $language);

                        if ($hotelRoomRate) {
                            $response['hotel_room_rates'][] = $hotelRoomRate;
                        }
                    }
                }
            }
        }

        return $response;
    }

    public function formatSubmitBookingResponse(
        $bookingSession,
        $bookingCreationResult,
        $messages,
        Hotel $hotel
    ) {
        $isSuccessfully = $bookingCreationResult instanceof Order;

        $response = [
            'reference_id' => $bookingSession,
            'status' => $isSuccessfully ? 'Success' : 'Failure',
            'customer_support' => $this->getCustomerSupportData($hotel->getContactInformation())
        ];

        if ($isSuccessfully) {
            $response['reservation'] =
                $this->getReservationData($bookingCreationResult);
        } else {
            $response['problems'] = $messages;
        }

        return $response;
    }

    public function formatBookingVerificationResponse(?Order $order, $channelManagerOrderId)
    {
        $isCreated = !is_null($order);
        $response = [
            'problems' => [],
            'reference_id' => $channelManagerOrderId,
            'status' => $isCreated ? 'Success' : 'Failure',
            'customer_support' => $this->getCustomerSupportData($order->getFirstHotel()->getContactInformation())
        ];

        if ($isCreated) {
            $response['reservation'] = $this->getReservationData($order);
        }

        return $response;
    }

    public function formatBookingCancelResponse($removalStatus, Hotel $hotel, $orderId)
    {
        return [
            'partner_hotel_code' => $hotel->getId(),
            'reservation_id' => $orderId,
            "status" => $removalStatus,
            'cancellation_number' => $orderId,
            'customer_support' => $this->getCustomerSupportData($hotel->getContactInformation())
        ];
    }

    public function formatRoomInformationResponse($apiVersion, $hotelData, $language, $queryKey, Hotel $hotel)
    {
        $hotelRoomTypes = [];
        $config = $hotel->getTripAdvisorConfig();
        foreach ($hotel->getRoomTypes() as $roomType) {
            $taRoomType = $config->getTARoomTypeByMBHRoomTypeId($roomType->getId());
            if (!is_null($taRoomType) && $taRoomType->getIsEnabled()) {
                $hotelRoomTypes[] = $this->getRoomTypeData($roomType, $language);
            }
        }

        $tariffsData = [];
        foreach ($hotel->getTariffs() as $tariff) {
            $taTariff = $config->getTATariffByMBHTariffId($tariff->getId());
            if (!is_null($taTariff) && $taTariff->getIsEnabled()) {
                $tariffsData[] = $this->getTariffData($tariff);
            }
        }

        $response = [
            'api_version' => $apiVersion,
            'hotel_id' => $hotelData['ta_id'],
            'language_response' => [
                'code_str' => $language,
                'error_log' => ''
            ],
            'unique_query_key' => $queryKey,
            'hotel_room_types' => $hotelRoomTypes,
            'hotel_rate_plans' => $tariffsData
        ];

        return $response;
    }

    private function getAvailableHotelAmenities($amenities)
    {
        $availableAmenities = [];
        foreach ($amenities as $amenity) {
            if (in_array($amenity, array_keys(self::HOTEL_AMENITIES)) && !in_array($amenity, $availableAmenities)) {
                $availableAmenities[] = self::HOTEL_AMENITIES[$amenity];
            }
        }

        return $availableAmenities;
    }

    private function getSearchUrl($roomTypeId, \DateTime $begin, \DateTime $end, $adultsCount, $childrenCount, $locale)
    {
        $params = [
            'begin' => $begin->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
            'roomType' => $roomTypeId,
            'adults' => $adultsCount,
            'children' => $childrenCount,
            'locale' => $locale
        ];

        return $this->onlineFormUrl . '?' . http_build_query($params);
    }

    private function getCustomerSupportData(ContactInfo $contactInfo)
    {
        return [
            'phone_numbers' => [
                [
                    "contact" => $contactInfo->getPhoneNumber(),
                    "description" => "Support phone line"
                ]
            ],
            'emails' => [
                [
                    'contact' => $contactInfo->getEmail(),
                    'description' => 'Support email'
                ]
            ]
        ];
    }

    private function getTariffData(TripAdvisorTariff $tripAdvisorTariff)
    {
        $tariff = $tripAdvisorTariff->getTariff();

        /** @var Tariff $tariff */
        $tariffData = [
            'code' => $tariff->getId(),
            'name' => $tariff->getName(),
            'description' => $tariff->getDescription() ? $tariff->getDescription() : $tariff->getName(),
            'rate_amenities' => $this->getRateAmenities($tariff),
            'refundable' => $tripAdvisorTariff->getRefundableType(),
            //TODO: Надеюсь, что эти данные не потребуются
//            'cancellation_rules',
            'meal_plan' => $this->getRateMealPlanes($tariff)
        ];

        return $tariffData;
    }

    private function getReservationData(Order $order)
    {
        /** @var Package $orderFirstPackage */
        $orderFirstPackage = $order->getFirstPackage();
        /** @var Tourist $payer */
        $payer = $order->getPayer();
        $orderAdditionalData = $order->getAdditionalData();
        $countryCode = $orderAdditionalData['countryCode'];
        $currency = $orderAdditionalData['currency'];
        $language = $orderAdditionalData['language'];

        $cashDocumentsData = [];
        foreach ($order->getCashDocuments() as $cashDocument) {
            /** @var CashDocument $cashDocument */
            if ($cashDocument->getOperation() == 'in') {
                $cashDocType = 'rate';
            } else {
                $cashDocType = 'fee';
            }
            $cashDocumentsData[] = [
                'price' => $this->getPriceObject(
                    $this->currencyHandler->convertFromRub($cashDocument->getTotal(), $currency), $currency),
                //TODO: Мб потом появятся еще данные о налогах
                'type' => $cashDocType
            ];
        }

        $convertedOrderPrice = $this->currencyHandler->convertFromRub($order->getPrice(), $currency);
        $reservationData = [
            'reservation_id' => $order->getId(),
            'status' => 'Booked',
            //TODO: Правильно ли?
            'confirmation_url' => $this->confirmationPage . '?'
                . http_build_query(['sessionId' => $order->getChannelManagerId(), 'order' => $order->getId()]),
            'checkin_date' => $orderFirstPackage->getBegin(),
            'checkout_date' => $orderFirstPackage->getEnd(),
            'partner_hotel_code' => $orderFirstPackage->getHotel()->getId(),
            'hotel' => $this->getHotelDetails($orderFirstPackage->getHotel(), $language),
            'customer' => [
                'first_name' => $payer->getFirstName(),
                'last_name' => $payer->getLastName(),
                'phone_number' => $payer->getPhone(),
                'email' => $payer->getEmail(),
                'country' => $countryCode
            ],
            'rooms' => $this->getRoomStayData($order->getPackages()),
            'receipt' => [
                'line_items' => $cashDocumentsData,
                'final_price_at_booking' => $this->getPriceObject($convertedOrderPrice, $currency),
                //TODO: Пока что вроде как только при бронировании берется, но мб потом добавим
                'final_price_at_checkout' => $this->getPriceObject(0, $currency)
            ]
        ];

        return $reservationData;
    }

    /**
     * @param Package[] $packages
     * @return array
     */
    private function getRoomStayData($packages)
    {
        $roomStayData = [];
        foreach ($packages as $package) {
            /** @var Package $package */
            /** @var Tourist $mainTraveller */
            $mainTraveller = $package->getTourists()[0];
            $roomStayData[] = [
                'party' => [
                    'adults' => $package->getAdults(),
                    'children' => $package->getChildAges()
                ],
                'traveler_first_name' => $mainTraveller->getFirstName(),
                'traveler_last_name' => $mainTraveller->getLastName()
            ];
        }

        return $roomStayData;
    }

    private function getPriceObject($priceValue, $currency)
    {
        return [
            'amount' => $priceValue,
            'currency' => $currency
        ];
    }

    private function getHotelDetails(Hotel $hotel, $requestedLocale)
    {
        if ($this->isRequestedLanguageLocal($requestedLocale)) {
            $hotelName = $hotel->getName();
            $streetName = $hotel->getStreet();
            $cityName = $hotel->getCity()->getName();
            $regionName = $hotel->getRegion()->getName();
        } else {
            $hotelName = $hotel->getInternationalTitle();
            $streetName = $hotel->getInternationalStreetName();
            $cityName = $this->getTranslatableTitle($hotel->getCity());
            $regionName = $this->getTranslatableTitle($hotel->getRegion());
        }

        $hotelDetails = [
            'name' => $hotelName,
            'address1' => $streetName . ', ' . $hotel->getHouse(),
            'city' => $cityName,
            'state' => $regionName,
            'country' => $hotel->getCountry()->getIsoAlpha2(),
            'phone' => $hotel->getContactInformation()->getPhoneNumber(),
            'url' => $hotel->getTripAdvisorConfig()->getHotelUrl(),
            'hotel_amenities' => $this->getHotelAmenities($hotel),
            'photos' => $this->getHotelPhotoData($hotel),
            'checkinout_policy' => $hotel->getCheckinoutPolicy(),
            'checkin_time' => $this->arrivalTime . ':00',
            'checkout_time' => $this->departureTime . ':00',
            'hotel_smoking_policy' => ['custom' => [$hotel->getSmokingPolicy()]],
        ];

        if ($hotel->getLatitude()) {
            $hotelDetails['latitude'] = $hotel->getLatitude();
        }
        if ($hotel->getLongitude()) {
            $hotelDetails['longitude'] = $hotel->getLongitude();
        }
        if ($hotel->getZipCode()) {
            $hotelDetails['postal_code'] = $hotel->getZipCode();
        }

        return $hotelDetails;
    }

    private function getHotelRoomRates(
        SearchResult $result,
        $adultChildrenCombinations,
        $currency,
        TripAdvisorConfig $config,
        $requestedLanguage
    ) {
        $priceData = $this->getPriceDataByAdultsChildrenCombinations($adultChildrenCombinations, $result);
        if (!$priceData) {
            return false;
        }
        $resultPrice = $this->currencyHandler->convertFromRub($priceData['price'], $currency);

        $hotelRoomRates = [
            'hotel_room_type_code' => $result->getRoomType()->getId(),
            'hotel_rate_plan_code' => $result->getTariff()->getId(),
            'final_price_at_booking' => $this->getPriceObject($resultPrice, $currency),
            //TODO: Пока что 0, может быть впоследствии другим значением
            'final_price_at_checkout' => $this->getPriceObject(0, $currency),
            'line_items' => $this->getLineItems($resultPrice, $currency, $config->getPaymentType()),
            'payment_policy' => $config->getPaymentPolicy(),
            'rooms_remaining' => $result->getRoomsCount(),
            'partnerData' => [
                'pricesByDate' => $priceData['pricesByDate'],
                'roomTypeId' => $result->getRoomType()->getId(),
                'tariffId' => $result->getTariff()->getId(),
                'hotelId' => $result->getRoomType()->getHotel()->getId(),
                'language' => $requestedLanguage
            ],
        ];

        return $hotelRoomRates;
    }

    private function getPriceDataByAdultsChildrenCombinations($adultsChildrenCombinations, SearchResult $result)
    {
        $roomType = $result->getRoomType();
        $tariff = $result->getTariff();
        $adultsChildrenCounts = $this->orderHandler->getAdultsChildrenCount($adultsChildrenCombinations, $tariff);
        //Все ли кобминации количеств детей и взрослых имеют цену?
        $isAllHavenPrice = true;
        $resultPriceData = ['price' => 0, 'roomCount' => 0];
        foreach ($adultsChildrenCounts as $estimatedAdultsChildrenCount) {
            $dividedAdultsChildrenCombinations =
                $this->orderHandler->getDividedAdultsChildrenCombinations(
                    $estimatedAdultsChildrenCount['adultsCount'], $estimatedAdultsChildrenCount['childrenCount'],
                    $roomType->getTotalPlaces());
            foreach ($dividedAdultsChildrenCombinations as $combination) {
                $adultsChildrenCounts = $roomType->getAdultsChildrenCombination(
                    $combination['adults'], isset($combination['children']) ? $combination['children'] : 0);
                $adultsCount = $adultsChildrenCounts['adults'];
                $childrenCount = $adultsChildrenCounts['children'];
                $price = $result->getPrice($adultsCount, $childrenCount);
                if ($price) {
                    $resultPriceData['price'] += $price;
                    $resultPriceData['pricesByDate'][$adultsCount . '_' . $childrenCount] =
                        $result->getPricesByDate($adultsCount, $childrenCount);
                    $resultPriceData['roomCount']++;
                } else {
                    $isAllHavenPrice = false;
                    break 2;
                }
            }
        }

        if ($isAllHavenPrice && $result->getRoomsCount() >= count($adultsChildrenCounts)) {
            return $resultPriceData;
        }

        return false;
    }

    private function getRoomTypeData(RoomType $roomType, $locale)
    {
        if ($this->isRequestedLanguageLocal($locale)) {
            $roomTypeName = $roomType->getName();
        } else {
            $roomTypeName = $roomType->getInternationalTitle();
        }
        $roomTypeResponseData = [
            'code' => $roomType->getId(),
            'name' => $roomTypeName,
            'description' => $roomType->getDescription()
                ? $roomType->getDescription() : $roomTypeName,
            'photos' => $this->getRoomTypePhotoData($roomType),
            'room_amenities' => $this->getRoomAmenities($roomType),
            'max_occupancy' => [
                'number_of_adults' => $roomType->getTotalPlaces(),
                'number_of_children' => 0
            ],
            'bed_configurations' => $this->getBedConfiguration($roomType),
            'extra_bed_configurations' => [],
            'room_smoking_policy' => $roomType->getIsSmoking(),
            'room_view_type' => $this->getRoomViewTypes($roomType)
        ];

        if ($roomType->getRoomSpace()) {
            $roomTypeResponseData['room_size_value'] = $roomType->getRoomSpace();
            $roomTypeResponseData['room_size_units'] = 'square_meters';
        }

        return $roomTypeResponseData;
    }

    private function getRoomTypePhotoData(RoomType $roomType)
    {
        $imagesData = [];
        foreach ($roomType->getImages() as $image) {
            $roomTypeImageData = [];
            /** @var RoomTypeImage $image */
            $roomTypeImageData['url'] = $this->domainName . '/' . $image->getPath();
            if ($image->getWidth()) {
                $roomTypeImageData['width'] = $image->getWidth();
            }
            if ($image->getHeight()) {
                $roomTypeImageData['height'] = $image->getHeight();
            }
            $imagesData[] = $roomTypeImageData;
        }

        return $imagesData;
    }

    private function getRoomAmenities(RoomType $roomType)
    {
        return $this->getSortedAmenities($roomType->getFacilities(), self::ROOM_AMENITIES);
    }

    private function getHotelPhotoData(Hotel $hotel)
    {
        $imagesData = [];
        foreach ($hotel->getImages() as $image) {
            $roomTypeImageData = [];
            /** @var Image $image */
            $roomTypeImageData['url'] = $this->domainName . $this->uploaderHelper->asset($image, 'imageFile',
                    Image::class);
            if ($image->getWidth()) {
                $roomTypeImageData['width'] = $image->getWidth();
            }
            if ($image->getHeight()) {
                $roomTypeImageData['height'] = $image->getHeight();
            }
            $imagesData[] = $roomTypeImageData;
        }

        return $imagesData;
    }

    private function getContactInfo(ContactInfo $contactInfo)
    {
        return [
            'full_name' => $contactInfo->getFullName(),
            'email' => $contactInfo->getEmail(),
            'phone_number' => $contactInfo->getPhoneNumber()
        ];
    }

    private function getTranslatableTitle(TranslatableInterface $entity)
    {
        $entity->setTranslatableLocale('en_EN');
        $this->dm->refresh($entity);

        return $entity->getTitle();
    }

    private function getHotelAmenities(Hotel $hotel)
    {
        return $this->getSortedAmenities($hotel->getFacilities(), self::ROOM_AMENITIES);
    }

    private function getSortedAmenities($specifiedAmenities, $standardAmenities)
    {
        $amenities = [
            'standard' => [],
            'custom' => []
        ];

        foreach ($specifiedAmenities as $facility) {
            if (in_array($facility, array_keys($standardAmenities))) {
                if (!in_array($standardAmenities[$facility], $amenities['standard'])) {
                    $amenities['standard'][] = $standardAmenities[$facility];
                }
            } else {
                if (!in_array($facility, $amenities['custom'])) {
                    $amenities['custom'][] = $facility;
                }
            }
        }

        return $amenities;
    }

    private function isRequestedLanguageLocal($requestedLocale)
    {
        return $this->locale == substr($requestedLocale, strpos($requestedLocale, '_') + 1);
    }

    private function getBedConfiguration(RoomType $roomType)
    {
        $bedConfiguration = [];
        if (isset($roomType->getFacilities()['bed'])) {
            $bedConfiguration[] = [
                'type' => 'standard',
                'code' => 9,
                'count' => 1
            ];
        }
        if (isset($roomType->getFacilities()['double-bed'])) {
            $bedConfiguration[] = [
                'type' => 'standard',
                'code' => 1,
                'count' => 1
            ];
        }

        return $bedConfiguration;
    }

    private function getRoomViewTypes(RoomType $roomType)
    {
        $viewTypes = [];
        foreach ($roomType->getRoomViewsTypes() as $roomViewType) {
            if ($roomViewType->getOpenTravelCode()) {
                $viewTypes['standard'][] = $roomViewType->getOpenTravelCode();
            } else {
                $viewTypes['custom'][] = $roomViewType->getCodeName();
            }
        }

        return $viewTypes;
    }

    private function getRateAmenities(Tariff $tariff)
    {
        $defaultServiceNames = [];
        foreach ($tariff->getDefaultServices() as $defaultService) {
            $serviceCode = $defaultService->getService()->getCode();
            if (!in_array($serviceCode, array_keys(self::RATE_MEAL_TYPES))) {
                $defaultServiceNames[] = $defaultService->getService()->getCode();
            }
        }

        return $this->getSortedAmenities($defaultServiceNames, self::RATE_AMENITIES);
    }

    private function getRateMealPlanes(Tariff $tariff)
    {
        $rateMealPlanes = [];
        foreach ($tariff->getDefaultServices() as $service) {
            /** @var TariffService $service */
            $serviceCode = $service->getService()->getCode();

            if (in_array($serviceCode, array_keys(self::RATE_MEAL_TYPES))) {
                $rateMealPlanes['standard'][] = self::RATE_MEAL_TYPES[$serviceCode];
            }
        }

        return $rateMealPlanes;
    }

    private function getLineItems($price, $currency, $paymentType)
    {
        $lineItems = [];
        switch ($paymentType) {
            case 'in_hotel':
                $lineItems[] = [
                    "price" => $this->getPriceObject($price, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => true,
                    'description' => 'Base rate'
                ];
                break;
            case 'online_full':
                $lineItems[] = $lineItems[] = [
                    "price" => $this->getPriceObject($price, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => false,
                    'description' => 'Base rate'
                ];
                break;
            case 'online_half':
                $lineItems[] = $lineItems[] = [
                    "price" => $this->getPriceObject($price / 2, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => true,
                    'description' => 'Base rate'
                ];
                $lineItems[] = $lineItems[] = [
                    "price" => $this->getPriceObject($price / 2, $currency),
                    'type' => 'rate',
                    'paid_at_checkout' => false,
                    'description' => 'Base rate'
                ];
                break;
        }

        return $lineItems;
    }

    private function getAcceptedCardTypes(Hotel $hotel)
    {
        $acceptedCardTypeCodes = [];
        foreach ($hotel->getAcceptedCardTypes() as $acceptedCardType) {
            /** @var CardType $acceptedCardType */
            $cardCode = $acceptedCardType->getCardCode();
            if (in_array($cardCode, self::TRIP_ADVISOR_AVAILABLE_CARD_TYPES)
                && !in_array($cardCode, $acceptedCardTypeCodes)
            ) {
                $acceptedCardTypeCodes[] = $acceptedCardType->getCardCode();
            }
        }

        return $acceptedCardTypeCodes;
    }
}