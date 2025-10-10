/******/ (() => { // webpackBootstrap
/******/ 	// runtime can't be in strict mode because a global variable is assign and maybe created.
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _inheritsLoose)
/* harmony export */ });
/* harmony import */ var _setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./setPrototypeOf.js */ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js");

function _inheritsLoose(t, o) {
  t.prototype = Object.create(o.prototype), t.prototype.constructor = t, (0,_setPrototypeOf_js__WEBPACK_IMPORTED_MODULE_0__["default"])(t, o);
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/setPrototypeOf.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _setPrototypeOf)
/* harmony export */ });
function _setPrototypeOf(t, e) {
  return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) {
    return t.__proto__ = e, t;
  }, _setPrototypeOf(t, e);
}


/***/ }),

/***/ "./src/forum/components/SummaryPopup.tsx":
/*!***********************************************!*\
  !*** ./src/forum/components/SummaryPopup.tsx ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ SummaryPopup)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/Component */ "flarum/common/Component");
/* harmony import */ var flarum_common_Component__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_Component__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/common/helpers/icon */ "flarum/common/helpers/icon");
/* harmony import */ var flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4__);





var SummaryPopup = /*#__PURE__*/function (_Component) {
  function SummaryPopup() {
    var _this;
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    _this = _Component.call.apply(_Component, [this].concat(args)) || this;
    _this.loading = false;
    _this.error = null;
    _this.summaryData = null;
    _this.streamingContent = '';
    _this.eventSource = null;
    _this.isMinimized = false;
    _this.isClosing = false;
    // Drag state
    _this.isDragging = false;
    _this.dragStartX = 0;
    _this.dragStartY = 0;
    _this.popupX = 0;
    _this.popupY = 0;
    _this.popupElement = null;
    return _this;
  }
  (0,_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(SummaryPopup, _Component);
  var _proto = SummaryPopup.prototype;
  _proto.oninit = function oninit(vnode) {
    _Component.prototype.oninit.call(this, vnode);
    this.generateSummary();
  };
  _proto.oncreate = function oncreate(vnode) {
    _Component.prototype.oncreate.call(this, vnode);
    this.popupElement = vnode.dom;

    // Use saved position if available, otherwise use default position
    if (this.attrs.initialPosition) {
      this.popupX = this.attrs.initialPosition.x;
      this.popupY = this.attrs.initialPosition.y;
    } else {
      var rect = this.popupElement.getBoundingClientRect();
      this.popupX = window.innerWidth - rect.width - 20;
      this.popupY = 70;
    }
    this.updatePosition();
  };
  _proto.onremove = function onremove() {
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
    }
  };
  _proto.view = function view() {
    var _this2 = this;
    return m("div", {
      className: "SummaryPopup " + (this.isClosing ? 'is-closing' : '') + " " + (this.isMinimized ? 'is-minimized' : '') + " " + (this.isDragging ? 'is-dragging' : ''),
      style: "transform: translate(" + this.popupX + "px, " + this.popupY + "px);"
    }, m("div", {
      className: "SummaryPopup-header",
      onmousedown: function onmousedown(e) {
        return _this2.startDrag(e);
      }
    }, m("div", {
      className: "SummaryPopup-title"
    }, flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4___default()('fas fa-magic'), m("span", null, flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('datlechin-ai-summarize.forum.summary.title'))), m("div", {
      className: "SummaryPopup-actions"
    }, m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--icon Button--link",
      icon: this.isMinimized ? 'fas fa-chevron-down' : 'fas fa-chevron-up',
      onclick: function onclick() {
        _this2.isMinimized = !_this2.isMinimized;
      }
    }), m((flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_3___default()), {
      className: "Button Button--icon Button--link",
      icon: "fas fa-times",
      onclick: function onclick() {
        return _this2.close();
      }
    }))), !this.isMinimized && m("div", {
      className: "SummaryPopup-body"
    }, this.error && m("div", {
      className: "SummaryPopup-error Alert Alert--error"
    }, flarum_common_helpers_icon__WEBPACK_IMPORTED_MODULE_4___default()('fas fa-exclamation-circle'), m("span", null, this.error)), this.summaryData && m("div", {
      className: "SummaryPopup-content"
    }, m("div", {
      className: "SummaryPopup-summary " + (this.loading ? 'is-streaming' : '')
    }, this.summaryData.summary && m.trust(this.summaryData.summary)))));
  };
  _proto.generateSummary = function generateSummary() {
    var _this3 = this;
    if (this.loading) return;
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
    }
    this.loading = true;
    this.error = null;
    this.summaryData = null;
    this.streamingContent = '';
    m.redraw();
    var url = flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().forum.attribute('apiUrl') + '/ai-summarize/discussions?id=' + this.attrs.discussion.id() + '&stream=1';
    this.eventSource = new EventSource(url);
    this.eventSource.onmessage = function (event) {
      if (event.data === '[DONE]') {
        var _this3$eventSource;
        (_this3$eventSource = _this3.eventSource) == null || _this3$eventSource.close();
        _this3.eventSource = null;
        _this3.loading = false;
        m.redraw();
        return;
      }
      try {
        var data = JSON.parse(event.data);
        if (data.type === 'start') {
          _this3.summaryData = {
            summary: ''
          };
        } else if (data.type === 'content') {
          _this3.streamingContent += data.data;
          if (!_this3.summaryData) {
            _this3.summaryData = {
              summary: ''
            };
          }
          _this3.summaryData.summary = _this3.streamingContent;
        } else if (data.type === 'complete') {
          if (!_this3.summaryData) {
            _this3.summaryData = {
              summary: ''
            };
          }
          _this3.summaryData.summary = data.data.html;
        }
        m.redraw();
      } catch (e) {
        console.error('Failed to parse SSE data:', e);
      }
    };
    this.eventSource.onerror = function (error) {
      var _this3$eventSource2;
      console.error('EventSource error:', error);
      (_this3$eventSource2 = _this3.eventSource) == null || _this3$eventSource2.close();
      _this3.eventSource = null;
      var errorMessage = flarum_forum_app__WEBPACK_IMPORTED_MODULE_1___default().translator.trans('datlechin-ai-summarize.forum.summary.error');
      _this3.error = typeof errorMessage === 'string' ? errorMessage : String(errorMessage);
      _this3.loading = false;
      m.redraw();
    };
  };
  _proto.close = function close() {
    var _this4 = this;
    this.isClosing = true;
    m.redraw();
    setTimeout(function () {
      if (_this4.eventSource) {
        _this4.eventSource.close();
        _this4.eventSource = null;
      }
      _this4.attrs.onclose();
    }, 300);
  };
  _proto.startDrag = function startDrag(e) {
    var _this5 = this;
    var target = e.target;
    if (target.closest('.Button')) {
      return;
    }
    e.preventDefault();
    this.isDragging = true;
    this.dragStartX = e.clientX - this.popupX;
    this.dragStartY = e.clientY - this.popupY;
    var handleMouseMove = function handleMouseMove(e) {
      return _this5.onDrag(e);
    };
    var handleMouseUp = function handleMouseUp() {
      return _this5.stopDrag(handleMouseMove);
    };
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
    m.redraw();
  };
  _proto.onDrag = function onDrag(e) {
    if (!this.isDragging) return;
    e.preventDefault();
    this.popupX = e.clientX - this.dragStartX;
    this.popupY = e.clientY - this.dragStartY;
    if (this.popupElement) {
      var rect = this.popupElement.getBoundingClientRect();
      var maxX = window.innerWidth - rect.width;
      var maxY = window.innerHeight - rect.height;
      this.popupX = Math.max(0, Math.min(this.popupX, maxX));
      this.popupY = Math.max(0, Math.min(this.popupY, maxY));
    }
    this.updatePosition();
    m.redraw();
  };
  _proto.stopDrag = function stopDrag(handleMouseMove) {
    this.isDragging = false;
    document.removeEventListener('mousemove', handleMouseMove);

    // Save position when drag ends
    if (this.attrs.onPositionChange) {
      this.attrs.onPositionChange(this.popupX, this.popupY);
    }
    m.redraw();
  };
  _proto.updatePosition = function updatePosition() {
    if (this.popupElement) {
      this.popupElement.style.transform = "translate(" + this.popupX + "px, " + this.popupY + "px)";
    }
  };
  return SummaryPopup;
}((flarum_common_Component__WEBPACK_IMPORTED_MODULE_2___default()));


/***/ }),

/***/ "./src/forum/index.ts":
/*!****************************!*\
  !*** ./src/forum/index.ts ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/forum/app */ "flarum/forum/app");
/* harmony import */ var flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/common/extend */ "flarum/common/extend");
/* harmony import */ var flarum_common_extend__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_common_models_Discussion__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/common/models/Discussion */ "flarum/common/models/Discussion");
/* harmony import */ var flarum_common_models_Discussion__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_common_models_Discussion__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_common_Model__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/common/Model */ "flarum/common/Model");
/* harmony import */ var flarum_common_Model__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_common_Model__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/forum/utils/DiscussionControls */ "flarum/forum/utils/DiscussionControls");
/* harmony import */ var flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/common/components/Button */ "flarum/common/components/Button");
/* harmony import */ var flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _utils_PopupManager__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./utils/PopupManager */ "./src/forum/utils/PopupManager.ts");







flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().initializers.add('datlechin/flarum-ai-summarize', function () {
  (flarum_common_models_Discussion__WEBPACK_IMPORTED_MODULE_2___default().prototype).canSummarize = flarum_common_Model__WEBPACK_IMPORTED_MODULE_3___default().attribute('canSummarize');
  (0,flarum_common_extend__WEBPACK_IMPORTED_MODULE_1__.extend)((flarum_forum_utils_DiscussionControls__WEBPACK_IMPORTED_MODULE_4___default()), 'userControls', function (items, discussion) {
    var enabled = flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().forum.attribute('datlechin-ai-summarize.enabled');
    var minPosts = Number(flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().forum.attribute('datlechin-ai-summarize.minPosts') || 3);
    if (!enabled || !(discussion.canSummarize != null && discussion.canSummarize())) {
      return;
    }
    var commentCount = discussion.commentCount();
    if (!commentCount || commentCount < minPosts) {
      return;
    }
    items.add('aiSummarize', flarum_common_components_Button__WEBPACK_IMPORTED_MODULE_5___default().component({
      icon: 'fas fa-magic',
      onclick: function onclick() {
        _utils_PopupManager__WEBPACK_IMPORTED_MODULE_6__["default"].show(discussion);
      }
    }, flarum_forum_app__WEBPACK_IMPORTED_MODULE_0___default().translator.trans('datlechin-ai-summarize.forum.discussion_controls.summarize_button')), -10);
  });
});

/***/ }),

/***/ "./src/forum/utils/PopupManager.ts":
/*!*****************************************!*\
  !*** ./src/forum/utils/PopupManager.ts ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _components_SummaryPopup__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../components/SummaryPopup */ "./src/forum/components/SummaryPopup.tsx");

var PopupManager = /*#__PURE__*/function () {
  function PopupManager() {
    this.container = null;
    this.isOpen = false;
    this.savedPosition = null;
  }
  var _proto = PopupManager.prototype;
  _proto.init = function init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'summary-popup-container';
      document.body.appendChild(this.container);
    }
  };
  _proto.show = function show(discussion) {
    var _this = this;
    this.init();
    if (this.isOpen) {
      this.close();
    }
    this.isOpen = true;
    m.mount(this.container, {
      view: function view() {
        if (!_this.isOpen) return null;
        return m(_components_SummaryPopup__WEBPACK_IMPORTED_MODULE_0__["default"], {
          discussion: discussion,
          onclose: function onclose() {
            return _this.close();
          },
          initialPosition: _this.savedPosition,
          onPositionChange: function onPositionChange(x, y) {
            return _this.savePosition(x, y);
          }
        });
      }
    });
  };
  _proto.close = function close() {
    this.isOpen = false;
    if (this.container) {
      m.mount(this.container, null);
    }
  };
  _proto.savePosition = function savePosition(x, y) {
    this.savedPosition = {
      x: x,
      y: y
    };
  };
  return PopupManager;
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (new PopupManager());

/***/ }),

/***/ "flarum/common/Component":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['common/Component']" ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/Component'];

/***/ }),

/***/ "flarum/common/Model":
/*!*****************************************************!*\
  !*** external "flarum.core.compat['common/Model']" ***!
  \*****************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/Model'];

/***/ }),

/***/ "flarum/common/components/Button":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/components/Button']" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/components/Button'];

/***/ }),

/***/ "flarum/common/extend":
/*!******************************************************!*\
  !*** external "flarum.core.compat['common/extend']" ***!
  \******************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/extend'];

/***/ }),

/***/ "flarum/common/helpers/icon":
/*!************************************************************!*\
  !*** external "flarum.core.compat['common/helpers/icon']" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/helpers/icon'];

/***/ }),

/***/ "flarum/common/models/Discussion":
/*!*****************************************************************!*\
  !*** external "flarum.core.compat['common/models/Discussion']" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['common/models/Discussion'];

/***/ }),

/***/ "flarum/forum/app":
/*!**************************************************!*\
  !*** external "flarum.core.compat['forum/app']" ***!
  \**************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['forum/app'];

/***/ }),

/***/ "flarum/forum/utils/DiscussionControls":
/*!***********************************************************************!*\
  !*** external "flarum.core.compat['forum/utils/DiscussionControls']" ***!
  \***********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = flarum.core.compat['forum/utils/DiscussionControls'];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!******************!*\
  !*** ./forum.ts ***!
  \******************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _src_forum__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/forum */ "./src/forum/index.ts");

})();

module.exports = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=forum.js.map