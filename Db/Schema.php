<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic\Db;

use DI\DependencyException;
use DI\NotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Modules\Database\Migration;

class Schema extends Migration {

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(): void {
        if (!$this->schema()->hasTable("stat_row")) {
            $this->schema()->create("stat_row", function(Blueprint $table){
                $table->engine = "InnoDB";
                $table->increments("id");
                $table->string("session_id");
                $table->mediumText("_server");
                $table->dateTime("created_at");
                $table->dateTime("updated_at");
                $table->index("id");
            });
        }

        if (!$this->schema()->hasTable("stats")) {
            $this->schema()->create("stats", function(Blueprint $table){
                $table->engine = "InnoDB";
                $table->increments("id");
                $table->string("session_id");
                $table->string("ip")->nullable();
                $table->string("os")->nullable();
                $table->string("platform")->nullable();
                $table->string("country")->nullable();
                $table->string("city")->nullable();
                $table->string("referer")->nullable();
                $table->dateTime("created_at");
                $table->dateTime("updated_at");
                $table->index("id");
            });
        }
    }

    public function update(): void {
        // comment explaining why the method is empty
    }

    public function delete(): void {
        // comment explaining why the method is empty
    }

}
