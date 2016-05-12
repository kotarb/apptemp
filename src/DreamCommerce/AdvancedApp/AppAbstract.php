<?php

namespace DreamCommerce\AdvancedApp;

use DreamCommerce\BugTracker\Collector\Psr3Collector;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use DreamCommerce\BugTracker\Http\Client\GuzzleWrapper;
use BugTracker\Collector\QueueCollector;
use DreamCommerce\BugTracker\Collector\JiraCollector;

/**
 * Class AppAbstract
 */
abstract class AppAbstract
{
    /**
     * @var \DreamCommerce\BugTracker\Collector\CollectorInterface
     */
    protected $apiCollector;

    /**
     * @var \DreamCommerce\BugTracker\Collector\CollectorInterface
     */
    protected $appCollector;

    /**
     * @var \DreamCommerce\BugTracker\Collector\CollectorInterface
     */
    protected $billingCollector;

    /**
     * @var \DreamCommerce\BugTracker\Collector\CollectorInterface
     */
    protected $externalCollector;

    /**
     * @var \DreamCommerce\BugTracker\Collector\JiraCollector
     */
    protected $jiraCollector;

    /**
     * @var array configuration storage
     */
    public $config = array();

    /**
     * @return BugTracker\Collector\QueueCollector
     */
    public function getApiCollector()
    {
        if($this->apiCollector === null) {
            $logLevel = $this->config['apiLogLevel'];
            $jiraLogLevel = $this->config['jiraLogLevel'];

            $jiraCollector = $this->getJiraCollector();
            $apiLogger = new Logger('API');
            $apiLogger->pushHandler(new StreamHandler(BASE_DIR.'/logs/shop_api_error.log', constant('Monolog\Logger::'.$logLevel)));
            $apiCollector = new Psr3Collector(array(
                'logger' => $apiLogger
            ));

            $apiQueueCollector = new QueueCollector();
            $apiQueueCollector->registerCollector($apiCollector, constant('\Psr\Log\LogLevel::'.$logLevel), QueueCollector::PRIORITY_HIGH);
            $apiQueueCollector->registerCollector($jiraCollector, constant('\Psr\Log\LogLevel::'.$jiraLogLevel), QueueCollector::PRIORITY_LOW);
            $this->apiCollector = $apiQueueCollector;
        }

        return $this->apiCollector;
    }

    /**
     * @return BugTracker\Collector\QueueCollector
     */
    public function getAppCollector()
    {
        if($this->appCollector === null) {
            $logLevel = $this->config['appLogLevel'];
            $jiraLogLevel = $this->config['jiraLogLevel'];

            $jiraCollector = $this->getJiraCollector();
            $appLogger = new Logger('APP');
            $appLogger->pushHandler(new StreamHandler(BASE_DIR.'/logs/application_error.log', constant('Monolog\Logger::'.$logLevel)));
            $appCollector = new Psr3Collector(array(
                'logger' => $appLogger
            ));
            $appQueueCollector = new QueueCollector();
            $appQueueCollector->registerCollector($appCollector, constant('\Psr\Log\LogLevel::'.$logLevel), QueueCollector::PRIORITY_HIGH);
            $appQueueCollector->registerCollector($jiraCollector, constant('\Psr\Log\LogLevel::'.$jiraLogLevel), QueueCollector::PRIORITY_LOW);
            $this->appCollector = $appQueueCollector;
        }

        return $this->appCollector;
    }

    /**
     * @return BugTracker\Collector\QueueCollector
     */
    public function getBillingCollector()
    {
        if($this->billingCollector === null) {
            $logLevel = $this->config['billingLogLevel'];
            $jiraLogLevel = $this->config['jiraLogLevel'];

            $jiraCollector = $this->getJiraCollector();
            $billingLogger = new Logger('BILLING');
            $billingLogger->pushHandler(new StreamHandler(BASE_DIR.'/logs/billing_error.log', constant('Monolog\Logger::'.$logLevel)));
            $billingCollector = new Psr3Collector(array(
                'logger' => $billingLogger
            ));

            $billingQueueCollector = new QueueCollector();
            $billingQueueCollector->registerCollector($billingCollector, constant('\Psr\Log\LogLevel::'.$logLevel), QueueCollector::PRIORITY_HIGH);
            $billingQueueCollector->registerCollector($jiraCollector, constant('\Psr\Log\LogLevel::'.$jiraLogLevel), QueueCollector::PRIORITY_LOW);
            $this->billingCollector = $billingQueueCollector;
        }

        return $this->billingCollector;
    }

    /**
     * @return BugTracker\Collector\QueueCollector
     */
    public function getExternalCollector()
    {
        if($this->externalCollector === null) {
            $logLevel = $this->config['billingLogLevel'];
            $jiraLogLevel = $this->config['jiraLogLevel'];

            $jiraCollector = $this->getJiraCollector();
            $externalLogger = new Logger('BILLING');
            $externalLogger->pushHandler(new StreamHandler(BASE_DIR.'/logs/billing_error.log', constant('Monolog\Logger::'.$logLevel)));
            $externalCollector = new Psr3Collector(array(
                'logger' => $externalLogger
            ));

            $billingQueueCollector = new QueueCollector();
            $billingQueueCollector->registerCollector($externalCollector, constant('\Psr\Log\LogLevel::'.$logLevel), QueueCollector::PRIORITY_HIGH);
            $billingQueueCollector->registerCollector($jiraCollector, constant('\Psr\Log\LogLevel::'.$jiraLogLevel), QueueCollector::PRIORITY_LOW);
            $this->externalCollector = $billingQueueCollector;
        }

        return $this->externalCollector;
    }

    /**
     * @return \DreamCommerce\BugTracker\Collector\JiraCollector
     */
    public function getJiraCollector()
    {
        if($this->jiraCollector === null) {
            $httpClient = new GuzzleWrapper(new \GuzzleHttp\Client());

            $jiraConfig = $this->config['jira'];
            $jiraConfig['http_client'] = $httpClient;
            $this->jiraCollector = new JiraCollector($jiraConfig);
        }

        return $this->jiraCollector;
    }

    /**
     * return (and instantiate if needed) a db connection
     * @return \PDO
     */
    public function db()
    {
        static $handle = null;
        if (!$handle) {
            $handle = new \PDO(
                $this->config['db']['connection'],
                $this->config['db']['user'],
                $this->config['db']['pass']
            );
            $handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return $handle;
    }

}
