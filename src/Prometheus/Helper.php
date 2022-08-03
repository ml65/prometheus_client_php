<?php

namespace Prometheus;

class Helper
{
    protected static $_instance;

    protected $registry;

    function __construct()
    {
        \Prometheus\Storage\Redis::setDefaultOptions(
            [
                'host' => $_ENV['REDIS_SENTINEL_HOST'],
                'port' => $_ENV['REDIS_SENTINEL_PORT']?$_ENV['REDIS_SENTINEL_PORT']:26379,
                'password' => $_ENV['REDIS_SENTINEL_PSWD'],
                'sentinels' => true,
                'master_name' => $_ENV['REDIS_SENTINEL_USER'],
                'timeout' => 0.1, // in seconds
                'read_timeout' => '10', // in seconds
                'persistent_connections' => false
            ]
        );
        $this->registry = \Prometheus\CollectorRegistry::getDefault();


    }
    public static function init()
    {
        if( is_null(self::$_instance))
            self::$instance = new Helper();
        return self::$_inctance;
    }

    public function counterInc($namespace, $name, $help, $labels = [])
    {
        $counter = $this->registry->getOrRegisterCounter($namespace, $name, $help, $labels);
        $counter->inc();

    }

    public function render()
    {
        $renderer = new \Prometheus\RenderTextFormat();
        return $renderer->render($this->registry->getMetricFamilySamples());

    }

}