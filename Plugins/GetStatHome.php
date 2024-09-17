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

class GetStatHome extends AbstractPlugin {

    use StatisticTrait;

    /**
     * @return array
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function process(): array {
        return $this->getStatisticModel()->getStatHome();
    }

}
