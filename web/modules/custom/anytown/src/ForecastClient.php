<?php

declare(strict_types = 1);

namespace Drupal\anytown;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Forecast retrieval API client.
 */
class ForecastClient implements ForecastClientInterface {

  /**
   * Guzzle HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Caching service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Construct a forecast API client.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   Guzzle HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Caching service.
   */
  public function __construct(ClientInterface $httpClient, LoggerChannelFactoryInterface $logger_factory, CacheBackendInterface $cacheBackend) {
    $this->httpClient = $httpClient;
    $this->logger = $logger_factory->get('anytown');
    $this->cache = $cacheBackend;
  }

  /**
   * {@inheritDoc}
   */
  public function getForecastData(string $url, bool $reset_cache = FALSE) : ?array {
    // Create a unique cache ID using the URL.
    $cache_id = 'anytown:forecast:' . md5($url);

    // Look for an existing cache record.
    $data = $this->cache->get($cache_id);

    // If we find one, we can use the cached data, unless specifically asked not
    // to.
    if (!$reset_cache && $data) {
      $forecast = $data->data;
    }
    // If not, we need to request fresh data from the API.
    else {
      try {
        $response = $this->httpClient->get($url);
        $json = json_decode($response->getBody()->getContents());
      }
      catch (GuzzleException $e) {
        $this->logger->warning($e->getMessage());
        return NULL;
      }

      $forecast = [];
      foreach ($json->list as $day) {
        $forecast[$day->day] = [
          'weekday' => ucfirst($day->day),
          'description' => $day->weather[0]->description,
          'high' => $this->kelvinToFahrenheit($day->main->temp_max),
          'low' => $this->kelvinToFahrenheit($day->main->temp_min),
          'icon' => $day->weather[0]->icon,
        ];
      }

      // Store the calculated data in the cache for next time, or until it's
      // more than 1 hour old.
      $this->cache->set($cache_id, $forecast, strtotime('+1 hour'));
    }

    return $forecast;
  }

  /**
   * Helper to convert temperature values form Kelvin to Fahrenheit.
   *
   * @param float $kelvin
   *   Temperature in Kelvin.
   *
   * @return float
   *   Temperature in Fahrenheit, rounded to the nearest int.
   */
  public static function kelvinToFahrenheit(float $kelvin) : float {
    return round(($kelvin - 273.15) * 9 / 5 + 32);
  }

}
