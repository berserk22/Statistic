<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic\Plugins;

use DI\DependencyException;
use DI\NotFoundException;
use Modules\Statistic\StatisticTrait;
use Modules\View\AbstractPlugin;

class GetRawDecoded extends AbstractPlugin {

    use StatisticTrait;

    protected array $stat = [
        "ip"=>"",
        "platform"=>""
    ];

    /**
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(): string {
        $raw = $this->getStatisticManager()->getStatRowEntity()::where("id", "=", 341)->first();
        $userAgent = $raw->getServer()['HTTP_USER_AGENT'];
        $platform = 'Unbekannt';
        if (str_contains($userAgent, 'Windows')) {
            $platform = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $platform = 'Mac';
        } elseif (str_contains($userAgent, 'Linux')) {
            $platform = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $platform = 'Android';
        } elseif (str_contains($userAgent, 'iOS')) {
            $platform = 'iOS';
        } elseif (str_contains($userAgent, 'Unix')) {
            $platform = 'Unix';
        }
        return $platform;
    }

}
