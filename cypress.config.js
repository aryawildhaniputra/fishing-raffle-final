const { defineConfig } = require('cypress')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://127.0.0.1:8000',
    viewportWidth: 1920,
    viewportHeight: 1080,
    video: true,
    screenshotOnRunFailure: true,
    redirectionLimit: 200, // Increase for recursive draw all groups
    chromeWebSecurity: false, // Disable web security to prevent CORS issues
    defaultCommandTimeout: 10000, // Increase timeout to 10 seconds
    pageLoadTimeout: 60000, // Increase page load timeout
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
})
