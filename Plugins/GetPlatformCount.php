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

class GetPlatformCount extends AbstractPlugin {

    use StatisticTrait;

    /**
     * @param string $from
     * @param string $to
     * @return bool|string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(string $from, string $to): bool|string {
        return $this->getStatisticModel()->getPlatformCount($from, $to);
    }
}
