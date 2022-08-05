<?php

namespace Prometheus;

class Helper
{
    protected static $_instance;
    // name of All count
    protected static $countname = 'all_amocrm_request';
    protected static $counthelp = 'All request to amocrm';
    // name of count Error 429
    protected static $errorcount = 'error_429_counter';
    protected static $errorhelp = 'ERROR 429 counter';
    protected static $logpath = __DIR__ . '/../../../../../frontend/web/prometheus/logs/';
    protected static $registry;

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
            self::$registry = \Prometheus\CollectorRegistry::getDefault();
        } catch (\Exception $e) {
            //
        }
    }

    public static function init($domain = '', $code = '', $dir = '')
    {
        if( is_null(self::$_instance))
            self::$_instance = new Helper();

        if($domain) {
            // All request
            self::$_instance->counterInc('',self::$countname, self::$counthelp);
            // ERROR 429
            if($code == 429) {
                self::$_instance->counterInc('', self::$errorcount, self::$errorhelp);
                $fc = fopen(self::$logpath . date('Y-m-d') . '_log.log','a');
                fwrite($fc, date('Y-m-d H:i:s') . ' ' . $domain . ' ' . $dir . "\n");
                fclose($fc);
            }
        }
        return self::$_instance;
    }

    public function counterInc($namespace, $name, $help, $labels = [])
    {
        try {
            $counter = self::$registry->getOrRegisterCounter($namespace, $name, $help, $labels);
            $counter->inc();
        } catch (\Exception $e) {
            //
        }

    }

    public function render()
    {
        try {
            $renderer = new \Prometheus\RenderTextFormat();
            return $renderer->render(self::$registry->getMetricFamilySamples());
        } catch (\Exception $e) {
            return '';
        }

    }

}