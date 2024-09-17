<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic\Db\Models;

use Modules\Database\Model;

class StatRow extends Model {

    protected $table = "stat_row";

    /**
     * @return mixed
     */
    public function getServer(): mixed {
        return json_decode($this->_server, true);
    }

}
