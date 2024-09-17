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

class GetVisitorsCount extends AbstractPlugin {

    use StatisticTrait;

    /**
     * @param string $from
     * @param string $to
     * @param string $type
     * @return string|bool
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(string $from, string $to, string $type = "day"): string|bool {
        return $this->getStatisticModel()->getVisitorsCount($from, $to, $type);
    }

}
