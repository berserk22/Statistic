<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic;

use Core\Traits\App;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\MsgQueue\Manager\MsgManager;
use Modules\Statistic\Manager\StatManager;
use Modules\Statistic\Manager\StatModel;

trait StatisticTrait {

    use App;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @return StatManager
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getStatisticManager(): StatManager {
        return $this->getContainer()->get('Statistic\Manager');
    }

    /**
     * @return MsgManager
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getMsgManager(): MsgManager {
        return $this->getContainer()->get('MsgQueue\Manager');
    }

    /**
     * @return StatModel
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getStatisticModel(): StatModel {
        return $this->getContainer()->get('Statistic\Model');
    }

}
