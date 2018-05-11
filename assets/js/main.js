/*
 * This is entry point for application.
 *
 * Require JS will be used here to load all needed
 * dependencies (Vue at least).
 */
document.addEventListener('DOMContentLoaded', function () {
  require.config({
    paths: Object.assign(RC_APP_REQUIRE_FILES, {
      cjs: 'https://rawgit.com/guybedford/cjs/master/cjs',
      'amd-loader': 'https://rawgit.com/guybedford/amd-loader/master/amd-loader',
      bottle: 'https://cdnjs.cloudflare.com/ajax/libs/bottlejs/1.6.1/bottle.min',

      vue: 'https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.4/vue',
      vuex: 'https://cdnjs.cloudflare.com/ajax/libs/vuex/3.0.1/vuex',
      validate: 'https://cdn.jsdelivr.net/npm/vee-validate@latest/dist/vee-validate',

      jquery: 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min',
      axios: 'https://cdn.jsdelivr.net/npm/axios@0.18.0/dist/axios.min',
      humanizeDuration: 'https://cdnjs.cloudflare.com/ajax/libs/humanize-duration/3.14.0/humanize-duration.min',

      textFormatter: 'https://unpkg.com/sprintf-js@1.1.1/dist/sprintf.min',
      uiFramework: 'https://unpkg.com/@rebelcode/ui-framework@0.1.1/dist/static/js/uiFramework',
      stdLib: 'https://unpkg.com/@rebelcode/std-lib@0.1.2/dist/std-lib',
      calendar: 'https://unpkg.com/@rebelcode/vc-calendar@0.1.0/dist/vc-calender',
      repeater: 'https://unpkg.com/@rebelcode/vc-repeater@0.1.0/dist/vc-repeater',
      selectionList: 'https://unpkg.com/@rebelcode/vc-selection-list@0.1.0/dist/vc-selection-list',
      tabs: 'https://unpkg.com/@rebelcode/vc-tabs@0.1.0/dist/vc-tabs',
      datetimePicker: 'https://unpkg.com/@rebelcode/vc-datetime-picker@0.1.0/dist/vc-datetime-picker',

      datepicker: 'https://cdn.jsdelivr.net/npm/vuejs-datepicker@0.9.26/dist/build.min',
      timepicker: 'https://cdn.jsdelivr.net/npm/vue2-timepicker@0.1.4/dist/vue2-timepicker.min',

      fullCalendar: 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.8.0/fullcalendar.min',
      lodash: 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.min',
      moment: 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min',
      fastDeepEqual: 'https://cdn.jsdelivr.net/npm/fast-deep-equal@1.1.0/index.min',
      pluralize: 'https://cdnjs.cloudflare.com/ajax/libs/pluralize/7.0.0/pluralize.min',

      cfToggleable: 'https://unpkg.com/@rebelcode/vc-toggleable@0.1.3/dist/umd/lib.min',
      vueselect: 'https://unpkg.com/vue-select@2.4.0/dist/vue-select',
      wpListTable: 'https://unpkg.com/vue-wp-list-table@1.1.0/dist/vue-wp-list-table.common',
    })
  })

  var dependenciesList = [
    'app',
    'cfToggleable',
    'uiFramework',
    'bottle',
    'vue',
    'vuex',
    'validate',
    'stdLib',
    'axios',
    'calendar',
    'pluralize',
    'vueselect',
    'datetimePicker',
    'tabs',
    'cjs!datepicker',
    'timepicker',
    'repeater',
    'selectionList',
    'jquery',
    'fullCalendar',
    'lodash',
    'moment',
    'cjs!fastDeepEqual',
    'cjs!wpListTable',
    'textFormatter',
    'humanizeDuration'
  ]

  require(dependenciesList, function () {
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
    var serviceList = dependencies.app.services(dependencies, document)
    serviceList['APP_STATE'] = function () {
      return window.EDDBK_APP_STATE
    }
    serviceList['selectorList'] = function () {
      return [
        '#eddbk-bookings-page',
        '#eddbk-service-page',
        '#eddbk-bookings-screen-options'
      ]
    }
    for (var i = 0; i < Object.keys(serviceList).length; i++) {
      var serviceName = Object.keys(serviceList)[i]
      di.factory(serviceName, serviceList[serviceName])
    }
  }
})
