(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", , "CorePluginsAdmin"], factory);
	else if(typeof exports === 'object')
		exports["Slack"] = factory(require("CoreHome"), require("vue"), require("CorePluginsAdmin"));
	else
		root["Slack"] = factory(root["CoreHome"], root["Vue"], root["CorePluginsAdmin"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__, __WEBPACK_EXTERNAL_MODULE_a5a2__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "plugins/Slack/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "a5a2":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_a5a2__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "ReportParameters", function() { return /* reexport */ ReportParameters; });
__webpack_require__.d(__webpack_exports__, "SelectSlackChannel", function() { return /* reexport */ SelectSlackChannel; });

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Slack/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=5f5d6546

const _hoisted_1 = {
  key: 0
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _ctx$report;
  const _component_SelectSlackChannel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SelectSlackChannel");
  return _ctx.report && _ctx.report.type === 'slack' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SelectSlackChannel, {
    "is-slack-oauth-token-added": _ctx.isSlackOauthTokenAdded,
    "with-introduction": true,
    "model-value": (_ctx$report = _ctx.report) === null || _ctx$report === void 0 ? void 0 : _ctx$report.slackChannelID,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('change', 'slackChannelID', $event))
  }, null, 8, ["is-slack-oauth-token-added", "model-value"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/Slack/vue/src/ReportParameters/ReportParameters.vue?vue&type=template&id=5f5d6546

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Slack/vue/src/SelectSlackChannel/SelectSlackChannel.vue?vue&type=template&id=3a73ae18

const SelectSlackChannelvue_type_template_id_3a73ae18_hoisted_1 = {
  class: "slack"
};
const _hoisted_2 = {
  id: "slackChannelIDHelp",
  class: "inline-help-node"
};
const _hoisted_3 = ["innerHTML"];
const _hoisted_4 = ["innerHTML"];
function SelectSlackChannelvue_type_template_id_3a73ae18_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_Field = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Field");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SelectSlackChannelvue_type_template_id_3a73ae18_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_Field, {
    uicontrol: "text",
    name: "channelID",
    title: _ctx.translate('Slack_ChannelId'),
    class: "slack",
    "model-value": _ctx.modelValue,
    disabled: !_ctx.isSlackOauthTokenAdded,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('update:modelValue', $event))
  }, {
    "inline-help": Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_2, [!_ctx.isSlackOauthTokenAdded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 0,
      style: {
        "margin-right": "3.5px"
      },
      innerHTML: _ctx.$sanitize(_ctx.getSlackOAuthTokenNotAddedHelpText)
    }, null, 8, _hoisted_3)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: 1,
      innerHTML: _ctx.$sanitize(_ctx.getSlackChannelHelpText)
    }, null, 8, _hoisted_4))])]),
    _: 1
  }, 8, ["title", "model-value", "disabled"])]);
}
// CONCATENATED MODULE: ./plugins/Slack/vue/src/SelectSlackChannel/SelectSlackChannel.vue?vue&type=template&id=3a73ae18

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// EXTERNAL MODULE: external "CorePluginsAdmin"
var external_CorePluginsAdmin_ = __webpack_require__("a5a2");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Slack/vue/src/SelectSlackChannel/SelectSlackChannel.vue?vue&type=script&lang=ts



/* harmony default export */ var SelectSlackChannelvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    modelValue: String,
    isSlackOauthTokenAdded: {
      type: Boolean,
      default: false
    },
    withIntroduction: Boolean
  },
  emits: ['update:modelValue'],
  components: {
    Field: external_CorePluginsAdmin_["Field"]
  },
  methods: {
    linkTo(params) {
      return `?${external_CoreHome_["MatomoUrl"].stringify(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].urlParsed.value), params))}`;
    }
  },
  computed: {
    getSlackOAuthTokenNotAddedHelpText() {
      const link = this.linkTo({
        module: 'CoreAdminHome',
        action: 'generalSettings',
        updated: null
      });
      return Object(external_CoreHome_["translate"])('Slack_NoOauthTokenAdded', `<a href="${link}#/Slack" rel="noreferrer noopener" target="_blank">`, '</a>');
    },
    getSlackChannelHelpText() {
      return Object(external_CoreHome_["translate"])('Slack_SlackEnterYourSlackChannelIdHelpText', Object(external_CoreHome_["externalLink"])('https://matomo.org/faq/reports/how-to-integrate-slack-for-scheduled-reports/#step-2-set-up-slack-in-matomo'), '</a>');
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Slack/vue/src/SelectSlackChannel/SelectSlackChannel.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Slack/vue/src/SelectSlackChannel/SelectSlackChannel.vue



SelectSlackChannelvue_type_script_lang_ts.render = SelectSlackChannelvue_type_template_id_3a73ae18_render

/* harmony default export */ var SelectSlackChannel = (SelectSlackChannelvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/Slack/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts


const REPORT_TYPE = 'slack';
/* harmony default export */ var ReportParametersvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    report: {
      type: Object,
      required: true
    },
    isSlackOauthTokenAdded: {
      type: Boolean,
      default: false
    },
    defaultFormat: {
      type: String,
      required: true
    },
    defaultDisplayFormat: {
      type: Number,
      required: true
    },
    defaultEvolutionGraph: {
      type: Boolean,
      required: true
    }
  },
  components: {
    SelectSlackChannel: SelectSlackChannel
  },
  emits: ['change'],
  setup(props) {
    const {
      resetReportParametersFunctions,
      updateReportParametersFunctions,
      getReportParametersFunctions
    } = window;
    if (!resetReportParametersFunctions[REPORT_TYPE]) {
      resetReportParametersFunctions[REPORT_TYPE] = report => {
        report.displayFormat = props.defaultDisplayFormat;
        report.evolutionGraph = props.defaultEvolutionGraph;
        report.formatslack = props.defaultFormat;
        report.slackChannelID = '';
      };
    }
    if (!updateReportParametersFunctions[REPORT_TYPE]) {
      updateReportParametersFunctions[REPORT_TYPE] = report => {
        if (!(report !== null && report !== void 0 && report.parameters)) {
          return;
        }
        ['displayFormat', 'evolutionGraph', 'slackChannelID'].forEach(field => {
          if (field in report.parameters) {
            report[field] = report.parameters[field];
          }
        });
      };
    }
    if (!getReportParametersFunctions[REPORT_TYPE]) {
      getReportParametersFunctions[REPORT_TYPE] = report => ({
        displayFormat: report.displayFormat,
        evolutionGraph: report.evolutionGraph,
        slackChannelID: report.slackChannelID
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/Slack/vue/src/ReportParameters/ReportParameters.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/Slack/vue/src/ReportParameters/ReportParameters.vue



ReportParametersvue_type_script_lang_ts.render = render

/* harmony default export */ var ReportParameters = (ReportParametersvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/Slack/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=Slack.umd.js.map