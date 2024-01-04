(function (Drupal, once) {
  Drupal.behaviors.forecastToggle = {
    attach: function (context, settings) {
      // Use 'once' to ensure this runs only once per context
      once('forecast-toggle', 'div.weather_page--forecast', context).forEach(function (el) {
        // Initialize: hide 'div.long' and show 'div.short'.
        const long = el.querySelector('.long');
        const short = el.querySelector('.short');
        long.classList.add('visually-hidden');

        // Create and configure a button to toggle between thet wo.
        const toggleButton = document.createElement('button');
        toggleButton.textContent = Drupal.t('Toggle extended forecast');
        toggleButton.addEventListener('click', function () {
          long.classList.toggle('visually-hidden');
          short.classList.toggle('visually-hidden');
        });

        // Append the button to the page.
        document.querySelector('.weather_page--forecast').appendChild(toggleButton);
      });
    }
  };
})(Drupal, once);
