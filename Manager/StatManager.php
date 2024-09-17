<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic\Manager;

use Core\Traits\App;
use DI\DependencyException;
use DI\NotFoundException;

class StatManager {

    use App;

    /**
     * @var string
     */
    private string $statRow = "Statistic\StatRow";

    /**
     * @var string
     */
    private string $stats = "Statistic\Stats";

    /**
     * @return $this
     */
    public function initEntity(): static {
        if (!$this->getContainer()->has($this->statRow)){
            $this->getContainer()->set($this->statRow, function(){
                return 'Modules\Statistic\Db\Models\StatRow';
            });
        }

        if (!$this->getContainer()->has($this->stats)){
            $this->getContainer()->set($this->stats, function(){
                return 'Modules\Statistic\Db\Models\Stats';
            });
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getStatRowEntity(): mixed {
        return $this->getContainer()->get($this->statRow);
    }

    /**
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getStatsEntity(): mixed {
        return $this->getContainer()->get($this->stats);
    }

}
