require('./scss/app.scss')

/*
 * This is entry point for application.
 *
 * Require JS will be used here to load all needed
 * dependencies (Vue at least).
 */
document.addEventListener('DOMContentLoaded', function () {
  function map (object, mapFn) {
    return Object.keys(object).reduce(function(result, key) {
      result[key] = mapFn(key, object[key])
      return result
    }, {})
  }

  /**
   * Libraries that should be required. It automatically synced with `bower.json` using
   * Webpack's DefinePlugin.
   *
   * @see ./webpack.config.js
   */
  var EDDBK_REQUIRE_LIBS = BOWER_DEPS
  var dependenciesList = [
    'app',
    'cfToggleable',
    'uiFramework',
    'bottle',
    'vue',
    'vuex',
    'validate',
    'toasted',
    'stdLib',
    'bookingWizardComponents',
    'axios',
    'calendar',
    'pluralize',
    'vueselect',
    'datetimePicker',
    'tabs',
    'sha1',
    'cjs!datepicker',
    'timepicker',
    'repeater',
    'selectionList',
    'jquery',
    'fullCalendar',
    'lodash',
    'moment',
    'momentWithTimezone',
    'cjs!momentRange',
    'cjs!fastDeepEqual',
    'cjs!wpListTable',
    'textFormatter',
    'humanizeDuration',
    'vueColor'
  ]

  window.require.config({
    baseUrl: EDDBK_APP_STATE.scripts_base,
    paths: Object.assign(RC_APP_REQUIRE_FILES, map(EDDBK_REQUIRE_LIBS, function (key, cdnUrl) {
      // load local versions of `cjs!` loader dependencies to prevent timeouts.
      var localUrl = key + '/index'
      return dependenciesList.indexOf('cjs!' + key) !== -1 ? [localUrl , cdnUrl] : [cdnUrl,  localUrl]
    }))
  })

  window.require(dependenciesList, function () {
    var dependencies = {}

    for (var i = 0; i < dependenciesList.length; i++) {
      dependencies[dependenciesList[i].replace('cjs!', '')] = arguments[i]
    }

    var di = new dependencies.bottle()
    defineServices(di, dependencies)

    var container = new dependencies.uiFramework.Container.Container(di)
    var app = new dependencies.uiFramework.Core.App(container)
    app.init()
  })

  function defineServices (di, dependencies) {
    var applicationState = window.EDDBK_APP_STATE
    var serviceList = dependencies.app.services(dependencies, applicationState, document)
    serviceList['APP_STATE'] = function () {
      return applicationState
    }
    serviceList['selectorList'] = function () {
      return [
        '#eddbk-bookings-page',
        '#eddbk-service-page',
        '#eddbk-bookings-screen-options',
        '#eddbk-settings-page'
      ]
    }
    for (var i = 0; i < Object.keys(serviceList).length; i++) {
      var serviceName = Object.keys(serviceList)[i]
      di.factory(serviceName, serviceList[serviceName])
    }
  }
})
