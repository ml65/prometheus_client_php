<?php

namespace Prometheus;

class Helper
{
    protected static $_instance;

    protected $registry;

    function __construct()
    {
        try {
            \Prometheus\Storage\Redis::setDefaultOptions(
                [
                    'host' => $_ENV['REDIS_SENTINEL_HOST'],
                    'port' => $_ENV['REDIS_SENTINEL_PORT'] ??  26379,
                    'password' => $_ENV['REDIS_SENTINEL_PSWD'],
                    'sentinels' => true,
                    'master_name' => $_ENV['REDIS_SENTINEL_USER'],
                    'timeout' => 0.1, // in seconds
                    'read_timeout' => '10', // in seconds
                    'persistent_connections' => false
                ]
            );
            $this->registry = \Prometheus\CollectorRegistry::getDefault();
        } catch (\Exception $e) {
            //
        }


    }
    public static function init()
    {
        if( is_null(self::$_instance))
            self::$_instance = new Helper();
        return self::$_instance;
    }

    public function counterInc($namespace, $name, $help, $labels = [])
    {
        try {
            $counter = $this->registry->getOrRegisterCounter($namespace, $name, $help, $labels);
            $counter->inc();
        } catch (\Exception $e) {
            //
        }

    }

    public function render()
    {
        try {
            $renderer = new \Prometheus\RenderTextFormat();
            return $renderer->render($this->registry->getMetricFamilySamples());
        } catch (\Exception $e) {
            return '';
        }

    }

}