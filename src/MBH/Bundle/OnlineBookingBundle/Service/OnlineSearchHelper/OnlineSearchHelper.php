<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Lib\SearchQuery;

class OnlineSearchHelper
{

    /** @var array */
    private $options;

    /** @var  \SplObjectStorage */
    private $resultsGenerators;
    /** @var  Helper */
    private $helper;

    /**
     * OnlineSearchHelper constructor.
     * @param array $options
     * @param Helper $helper
     */
    public function __construct(array $options, Helper $helper)
    {
        $this->options = $options;
        $this->resultsGenerators = new \SplObjectStorage();
        $this->helper = $helper;
    }

    public function addGenerator(AbstractResultGenerator $generator)
    {
        $this->resultsGenerators->attach($generator);
    }

    public function getResults(OnlineSearchFormData $formInstance)
    {
        $results = [];
        foreach ($this->resultsGenerators as $generator) {
            $results[$generator->getType()] = $generator->getResults($formInstance)->toArray();
        }

        return $this->finishFilter($results);
    }

    private function finishFilter(
        array $searchResults
    ) {
        $result = [];
        foreach ($searchResults as $key => $sResults) {
            //Тут можно фильтровать
            $result = array_merge($result, $sResults);
        }

        return $result;
    }
}