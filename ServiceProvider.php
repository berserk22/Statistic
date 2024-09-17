<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Statistic;

use Core\Module\Provider;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Database\MigrationCollection;
use Modules\Statistic\Console\StatParser;
use Modules\Statistic\Db\Schema;
use Modules\Statistic\Manager\StatManager;
use Modules\Statistic\Manager\StatModel;
use Modules\View\PluginManager;
use Modules\View\ViewManager;

class ServiceProvider extends Provider {

    /**
     * @var array|string[]
     */
    protected array $plugins = [
        'getVisitorsCount'=>'\Modules\Statistic\Plugins\GetVisitorsCount',
        'getPlatformCount'=>'\Modules\Statistic\Plugins\GetPlatformCount',
        'getRawDecoded'=>'\Modules\Statistic\Plugins\GetRawDecoded',
        'getCountryCount'=>'\Modules\Statistic\Plugins\GetCountryCount',
        'getCityCount'=>'\Modules\Statistic\Plugins\GetCityCount',
        'getRefererCount'=>'\Modules\Statistic\Plugins\GetRefererCount',
        'getStatHome'=>'\Modules\Statistic\Plugins\GetStatHome',
    ];

    /**
     * @return string[]
     */
    public function console(): array {
        return [];
    }

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function afterInit(): void {
        $container = $this->getContainer();
        if ($container->has('Modules\Database\ServiceProvider::Migration::Collection')) {
            /* @var $databaseMigration MigrationCollection */
            $container->get('Modules\Database\ServiceProvider::Migration::Collection')->add(new Schema($this));
        }

        if ($container->has('ViewManager::View')) {
            /** @var $viewer ViewManager */
            $viewer = $container->get('ViewManager::View');
            $plugins = function(){
                $pluginManager = new PluginManager();
                $pluginManager->addPlugins($this->plugins);
                return $pluginManager->getPlugins();
            };
            $viewer->setPlugins($plugins());
        }

        if (!$container->has('Statistic\Manager')){
            $this->getContainer()->set('Statistic\Manager', function(){
                $manager = new StatManager($this);
                return $manager->initEntity();
            });
        }

        if (!$container->has('Statistic\Model')){
            $this->getContainer()->set('Statistic\Model', function(){
                return new StatModel($this);
            });
        }
    }

}
