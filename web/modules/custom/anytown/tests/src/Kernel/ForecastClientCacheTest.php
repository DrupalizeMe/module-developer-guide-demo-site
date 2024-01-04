<?php

namespace Drupal\Tests\anytown\Kernel;

use Drupal\anytown\ForecastClient;
use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * Tests the caching of the getForecastData method in ForecastClient.
 *
 * @group anytown
 * @covers \Drupal\anytown\ForecastClient::getForecastData()
 */
class ForecastClientCacheTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system', 'anytown'];

  /**
   * Test the caching of forecast data.
   */
  public function testForecastDataCaching() {
    // Mock the HTTP client.
    $testData = '{
    "list": [
      {
        "day": "friday",
        "main": {
          "temp_min": 272.15,
          "temp_max": 279.15
        },
        "weather": [
          {
            "description": "light snow",
            "icon": "https://raw.githubusercontent.com/erikflowers/weather-icons/master/svg/wi-day-snow.svg"
          }
        ]
      }
    ]}';
    $httpClientMock = $this->createMock(Client::class);
    $httpClientMock
      // The get method should only be called once, even though we request the
      // data twice. This is because the request should used cached data.
      ->expects($this->once())
      ->method('get')
      ->willReturn(new Response(200, [], $testData));

    // Create the ForecastClient instance.
    $forecastClient = new ForecastClient($httpClientMock, $this->container->get('logger.factory'), $this->container->get('cache.default'));

    // URL for the API call.
    $apiUrl = 'http://example.com/api';

    // First call - should fetch data from the API.
    $initialData = $forecastClient->getForecastData($apiUrl, TRUE);
    $this->assertNotEmpty($initialData, 'Initial API call should return data.');

    // Second call - should retrieve data from the cache.
    $cachedData = $forecastClient->getForecastData($apiUrl);
    $this->assertEquals($initialData, $cachedData, 'Data should be retrieved from the cache on subsequent calls.');

    // Re-create the ForecastClient instance, and the httpClient mock so that we
    // can make a 3rd call with the cache reset flag set to TRUE, which should
    // trigger calling the get method again.
    $httpClientMock = $this->createMock(Client::class);
    $httpClientMock
      ->expects($this->once())
      ->method('get')
      ->willReturn(new Response(200, [], $testData));
    $forecastClient = new ForecastClient($httpClientMock, $this->container->get('logger.factory'), $this->container->get('cache.default'));
    // First one should get cached data, and not make a 'get' call.
    $forecastClient->getForecastData($apiUrl);
    // Second one should bypass the cache and make a 'get' call.
    $forecastClient->getForecastData($apiUrl, TRUE);
  }

}
