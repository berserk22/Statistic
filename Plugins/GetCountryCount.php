<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic\Plugins;

use DI\DependencyException;
use DI\NotFoundException;
use GeoIp2\Exception\AddressNotFoundException;use Modules\Statistic\StatisticTrait;
use Modules\View\AbstractPlugin;

class GetCountryCount extends AbstractPlugin {

    use StatisticTrait;

    /**
     * @param string $from
     * @param string $to
     * @return bool|string
     * @throws AddressNotFoundException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(string $from, string $to):bool|string{
        return $this->getStatisticModel()->getCountryCount($from, $to);
    }
}
