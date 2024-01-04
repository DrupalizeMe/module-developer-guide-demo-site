<?php

namespace Drupal\Tests\anytown\Unit;

use Drupal\anytown\ForecastClient;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for ForecastClient service.
 */
class ForecastClientTest extends UnitTestCase {

  /**
   * Tests the kelvinToFahrenheit method.
   *
   * @covers \Drupal\anytown\ForecastClient::kelvinToFahrenheit()
   */
  public function testKelvinToFahrenheit() {
    // Example test cases.
    $testCases = [
      // Absolute zero.
      [
        'f' => -460,
        'k' => 0,
      ],
      // Freezing point of water.
      [
        'f' => 32,
        'k' => 273.15,
      ],
    ];

    foreach ($testCases as $case) {
      $fahrenheit = ForecastClient::kelvinToFahrenheit($case['k']);
      $this->assertEquals($case['f'], $fahrenheit, "Kelvin to Fahrenheit conversion failed for {$case['k']}K");
    }
  }

  /**
   * Tests getForecastData with an API failure.
   *
   * @covers \Drupal\anytown\ForecastClient::getForecastData()
   */
  public function testGetForecastDataApiFailure() {
    // Mocking the dependencies.
    $httpClientMock = $this->createMock(Client::class);
    $loggerFactoryMock = $this->createMock(LoggerChannelFactoryInterface::class);
    $loggerMock = $this->createMock(LoggerInterface::class);
    $cacheBackendMock = $this->createMock(CacheBackendInterface::class);

    // Setting up the httpClientMock to throw a GuzzleException.
    $httpClientMock->method('get')->willThrowException(new TransferException('API Request Failed'));

    // Configure the logger factory mock to return the logger mock.
    $loggerFactoryMock->method('get')->willReturn($loggerMock);

    // Expect the logger to record a warning.
    $loggerMock->expects($this->once())
      ->method('warning')
      ->with($this->equalTo('API Request Failed'));

    // Creating an instance of ForecastClient with mocked dependencies.
    $forecastClient = new ForecastClient($httpClientMock, $loggerFactoryMock, $cacheBackendMock);

    // Calling getForecastData and expecting NULL due to API failure.
    $result = $forecastClient->getForecastData('http://example.com/api', TRUE);
    $this->assertNull($result, 'Expected NULL on API request failure');
  }

}
