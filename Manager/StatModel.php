<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic\Manager;

use Core\Exception;use DI\DependencyException;
use DI\NotFoundException;
use GeoIp2\Exception\AddressNotFoundException;
use Latte\Compiler\Nodes\FragmentNode;
use MaxMind\Db\Reader\InvalidDatabaseException;use Modules\Statistic\StatisticTrait;
use GeoIp2\Database\Reader;

class StatModel {

    use StatisticTrait;

    /**
     * @var int
     */
    private int $day = 86400;

    /**
     * @var int
     */
    private int $week = 604800;

    /**
     * @var int
     */
    private int $month = 2629743;

    /**
     * @var string
     */
    private string $dateTimeEurope = "d.m.Y H:i:s";

    /**
     * @var string
     */
    private string $dateTimeAmerican = "Y-m-d H:i:s";

    /**
     * @var string
     */
    private string $dayStart = "d.m.Y 00:00:00";

    /**
     * @var string
     */
    private string $dayEnd = "d.m.Y 23:59:59";

    /**
     * @return array[]
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getStatHome(): array {
        $now = strtotime(date($this->dateTimeEurope));
        $now_start = strtotime(date($this->dateTimeEurope, $now-$this->day));
        $week = strtotime(date($this->dateTimeEurope, time()-$this->week));
        $month = strtotime(date($this->dateTimeEurope, time()-$this->month));

        return [
            "visitors"=>[
                "now"=>[
                    $this->getStatisticManager()
                        ->getStatsEntity()::select("id", "session_id")
                        ->whereBetween('created_at', [date($this->dateTimeAmerican, $now_start), date($this->dateTimeAmerican, $now)])
                        ->groupBy("session_id")->get()->count(),
                    0
                ],
                "week"=>[
                    $this->getStatisticManager()
                        ->getStatsEntity()::select("id", "session_id")
                        ->whereBetween('created_at', [date($this->dateTimeAmerican, $week), date($this->dateTimeAmerican, $now)])
                        ->groupBy("session_id")->get()->count(),
                    0
                ],
                "month"=>[
                    $this->getStatisticManager()
                        ->getStatsEntity()::select("id", "session_id")
                        ->whereBetween('created_at', [date($this->dateTimeAmerican, $month), date($this->dateTimeAmerican, $now)])
                        ->groupBy("session_id")->get()->count(),
                    0
                ]
            ],
            "referer"=>[
                $this->getStatisticManager()
                    ->getStatsEntity()::select("id", "session_id", "referer")
                    ->whereBetween('created_at', [date($this->dateTimeAmerican, $now_start), date($this->dateTimeAmerican, $now)])
                    ->get()->count(),
                0
            ],
            "email"=>[
                $this->getMsgManager()
                    ->getMsgMailEntity()::select("id")
                    ->whereBetween('created_at', [date($this->dateTimeAmerican, $now_start), date($this->dateTimeAmerican, $now)])
                    ->get()->count(),
                0
            ]
        ];
    }

    /**
     * @param array $raw
     * @return string
     */
    public function getPlatform(array $raw): string {
        $platform = 'Unbekannt';
        if (isset($raw['HTTP_USER_AGENT'])){
            $userAgent = $raw['HTTP_USER_AGENT'];
            $isBot = preg_match('/bot|crawl|slurp|spider/i', $userAgent);
            if ($isBot) {
                $platform = 'Crawl/Spider Bot';
            }
            elseif (str_contains($userAgent, 'Android')) {
                $platform = 'Android';
            }
            elseif (str_contains($userAgent, 'iPhone')) {
                $platform = 'iPhone';
            }
            elseif (str_contains($userAgent, 'Windows')) {
                $platform = 'Windows';
            }
            elseif (str_contains($userAgent, 'Mac')) {
                $platform = 'Mac';
            }
            elseif (str_contains($userAgent, 'Linux')) {
                $platform = 'Linux';
            }
            elseif (str_contains($userAgent, 'iOS')) {
                $platform = 'iOS';
            }
            elseif (str_contains($userAgent, 'Unix')) {
                $platform = 'Unix';
            }
        }
        return $platform;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $type
     * @return bool|string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getVisitorsCount(string $from, string $to, string $type = "day"): bool|string {
        if (strtotime($to)-strtotime($from)<(86400 * 4)){
            $type="hours";
        }
        elseif (strtotime($to)-strtotime($from)>(2629743 * 3)){
            $type="month";
        }
        $start = strtotime(date($this->dayStart, strtotime($from)));
        $step = $this->$type;
        $end = strtotime(date($this->dayEnd, strtotime($to)));

        if ($type === "hours") {
            $format = "d.m H:i";
        }
        elseif($type === "month") {
            $format = "m.Y";
        }
        else {
            $format = "d.m";
        }

        $liste = [];
        for($i = $start; $i<=$end; $i+=$step){
            $from = $i;
            $to = $i+$step;

            if ($format === "m.Y"){
                $liste[date($format, $from)] = $this->getStatisticManager()
                    ->getStatsEntity()::select("id", "session_id")
                    ->whereBetween('created_at', [date("Y-m-0 00:00:00", $from), date("Y-m-t 23:59:59", $to)])
                    ->groupBy("session_id")->get()->count();
            }
            else {
                $liste[date($format, $from)] = $this->getStatisticManager()
                    ->getStatsEntity()::select("id", "session_id")
                    ->whereBetween('created_at', [date($this->dateTimeAmerican, $from), date($this->dateTimeAmerican, $to)])
                    ->groupBy("session_id")->get()->count();
            }
        }
        return json_encode($liste);
    }

    /**
     * @param string $from
     * @param string $to
     * @return bool|string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getPlatformCount(string $from, string $to): bool|string {
        $start = strtotime(date($this->dayStart, strtotime($from)));
        $end = strtotime(date($this->dayEnd, strtotime($to)));

        $raw = $this->getStatisticManager()
            ->getStatsEntity()::select("id", "session_id", "os")
            ->whereBetween('created_at', [date($this->dateTimeAmerican, $start), date($this->dateTimeAmerican, $end)])
            ->groupBy("session_id")->get();

        $liste = [];
        foreach($raw as $item){
            if (!isset($liste[$item->os])) {
                $liste[$item->os] = 1;
            }
            else {
                $liste[$item->os]++;
            }
        }

        return json_encode($liste);
    }

    /**
     * @param string $from
     * @param string $to
     * @return bool|string
     * @throws AddressNotFoundException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getCountryCount(string $from, string $to): bool|string {
        $start = strtotime(date($this->dayStart, strtotime($from)));
        $end = strtotime(date($this->dayEnd, strtotime($to)));

        $raw = $this->getStatisticManager()
            ->getStatsEntity()::select("id", "session_id", "country")
            ->whereBetween('created_at', [date($this->dateTimeAmerican, $start), date($this->dateTimeAmerican, $end)])
            ->groupBy("session_id")->get();

        $liste = [];
        foreach($raw as $item){
            if ($item->country !== "Unbekannt" && !empty($item->country)){
                if (!isset($liste[$item->country])) {
                    $liste[$item->country] = 1;
                }
                else {
                    $liste[$item->country]++;
                }
            }
        }
        arsort($liste);
        return json_encode(array_slice($liste, 0, 8, true));
    }

    /**
     * @param string $from
     * @param string $to
     * @return bool|string
     * @throws AddressNotFoundException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getCityCount(string $from, string $to): bool|string {
        $start = strtotime(date($this->dayStart, strtotime($from)));
        $end = strtotime(date($this->dayEnd, strtotime($to)));

        $raw = $this->getStatisticManager()
            ->getStatsEntity()::select("id", "session_id", "city")
            ->whereBetween('created_at', [date($this->dateTimeAmerican, $start), date($this->dateTimeAmerican, $end)])
            ->groupBy("session_id")->get();

        $liste = [];
        foreach($raw as $item){
            if ($item->city !== "Unbekannt" && !empty($item->city)){
                if (!isset($liste[$item->city])) {
                    $liste[$item->city] = 1;
                }
                else {
                    $liste[$item->city]++;
                }
            }
        }
        arsort($liste);
        return json_encode(array_slice($liste, 0, 8, true));
    }

    /**
     * @param string $from
     * @param string $to
     * @return bool|string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getRefererCount(string $from, string $to): bool|string {
        $start = strtotime(date($this->dayStart, strtotime($from)));
        $end = strtotime(date($this->dayEnd, strtotime($to)));

        $raw = $this->getStatisticManager()
            ->getStatsEntity()::select("id", "session_id", "referer")
            ->whereBetween('created_at', [date($this->dateTimeAmerican, $start), date($this->dateTimeAmerican, $end)])
            ->get();

        $liste = [];
        foreach($raw as $item){
            if (!empty($item->referer)){
                if (!isset($liste[$item->referer])) {
                    $liste[$item->referer] = 1;
                }
                else {
                    $liste[$item->referer]++;
                }
            }
        }
        arsort($liste);
        return json_encode(array_slice($liste, 0, 8, true));
    }

    /**
     * @param array $raw
     * @return string|null
     * @throws AddressNotFoundException
     */
    public function getCountry(array $raw): string|null {
        $ipAddress = $raw['REMOTE_ADDR'];
        $country = "Unbekannt";
        if (!empty($ipAddress)){
            try {
                $reader = new Reader(realpath(__DIR__."/../")."/GeoIp2DB/GeoLite2-Country.mmdb", ["de"]);
                $record = $reader->country($ipAddress);
                $country = $record->country->name;
                $reader->close();
            } catch(InvalidDatabaseException $e){
                return $e->getMessage();
            }
        }
        return $country;
    }

    /**
     * @param array $raw
     * @return string|null
     * @throws AddressNotFoundException
     */
    public function getCity(array $raw): string|null {
        $ipAddress = $raw['REMOTE_ADDR'];
        $city = "Unbekannt";
        if (!empty($ipAddress)){
            try {
                $reader = new Reader(realpath(__DIR__."/../")."/GeoIp2DB/GeoLite2-City.mmdb", ["de"]);
                $record = $reader->city($ipAddress);
                $city = $record->city->name;
                $reader->close();
            } catch(InvalidDatabaseException $e){
                $city = "Unbekannt";
            }
        }
        return $city;
    }

    public function getReferer(array $raw): string|null{
        $referrer = $raw['HTTP_REFERER'];
        $platform = "";
        if (!empty($referrer)){
            if (str_contains($referrer, 'google.com')) {
                $platform = 'Google';
            }
            elseif (str_contains($referrer, 'googleads.com')) {
                $platform = 'Google Ads';
            }
            elseif (str_contains($referrer, 'bing.com')) {
                $platform = 'Bing';
            }
            elseif (str_contains($referrer, 'suche.t-online.de')) {
                $platform = 'T-Online';
            }
            elseif (str_contains($referrer, 'instagram.com')) {
                $platform = 'Instagram';
            }
            elseif (str_contains($referrer, 'facebook.com')) {
                $platform = 'Facebook';
            }
        }
        return $platform;
    }

}
