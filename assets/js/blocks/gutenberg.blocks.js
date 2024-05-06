/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/wp-events/index.js":
/*!***********************************!*\
  !*** ./blocks/wp-events/index.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const {
  __
} = wp.i18n;
const {
  registerBlockType
} = wp.blocks;
const {
  dateI18n,
  getSettings
} = wp.date;
const {
  PanelBody,
  PanelRow,
  Button,
  Dropdown,
  RangeControl,
  SelectControl,
  ToggleControl,
  RadioControl,
  DateTimePicker
} = wp.components;
var InspectorControls = wp.blockEditor.InspectorControls;
registerBlockType('wpea-block/wp-events', {
  title: __('WP Events'),
  description: __('Block for Display WP Events'),
  // icon: 'wordpress',
  icon: {
    foreground: '#0073AA',
    src: (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
      viewBox: "0 0 24 24"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("g", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
      d: "M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.05-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.762-7.57zM3.008 12c0 3.56 2.07 6.634 5.068 8.092L3.788 8.342c-.5 1.117-.78 2.354-.78 3.658zm15.06-.454c0-1.112-.398-1.88-.74-2.48-.456-.74-.883-1.368-.883-2.11 0-.825.627-1.595 1.51-1.595.04 0 .078.006.116.008-1.598-1.464-3.73-2.36-6.07-2.36-3.14 0-5.904 1.613-7.512 4.053.21.008.41.012.58.012.94 0 2.395-.114 2.395-.114.484-.028.54.684.057.74 0 0-.487.058-1.03.086l3.275 9.74 1.968-5.902-1.4-3.838c-.485-.028-.944-.085-.944-.085-.486-.03-.43-.77.056-.742 0 0 1.484.114 2.368.114.94 0 2.397-.114 2.397-.114.486-.028.543.684.058.74 0 0-.488.058-1.03.086l3.25 9.665.897-2.997c.456-1.17.684-2.137.684-2.907zm1.82-3.86c.04.286.06.593.06.924 0 .912-.17 1.938-.683 3.22l-2.746 7.94c2.672-1.558 4.47-4.454 4.47-7.77 0-1.564-.4-3.033-1.1-4.314zM12 22C6.486 22 2 17.514 2 12S6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"
    })))
  },
  category: 'widgets',
  keywords: [__('Events'), __('WP'), __('wp events')],
  attributes: {
    col: {
      type: 'number',
      default: 2
    },
    posts_per_page: {
      type: 'number',
      default: 12
    },
    past_events: {
      type: 'boolean',
      default: false
    },
    start_date: {
      type: 'string',
      default: ''
    },
    end_date: {
      type: 'string',
      default: ''
    },
    order: {
      type: 'string',
      default: 'ASC'
    },
    orderby: {
      type: 'string',
      default: 'event_start_date'
    },
    layout: {
      type: 'string',
      default: ''
    }
  },
  edit: _ref => {
    let {
      attributes,
      setAttributes
    } = _ref;
    const {
      col,
      posts_per_page,
      past_events,
      start_date,
      end_date,
      order,
      orderby,
      layout
    } = attributes;
    const settings = getSettings();
    const dateClassName = past_events === true ? 'wpea_hidden' : '';
    const {
      serverSideRender: ServerSideRender
    } = wp;
    const is12HourTime = /a(?!\\)/i.test(settings.formats.time.toLowerCase() // Test only the lower case a
    .replace(/\\\\/g, '') // Replace "//" with empty strings
    .split('').reverse().join('') // Reverse the string and test for "a" not followed by a slash
    );

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: __('WP Events Setting')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RangeControl, {
      label: __('Columns'),
      value: col || 2,
      onChange: value => setAttributes({
        col: value
      }),
      min: 1,
      max: 4
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RangeControl, {
      label: __('Events per page'),
      value: posts_per_page || 12,
      onChange: value => setAttributes({
        posts_per_page: value
      }),
      min: 1,
      max: 100
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      label: __('Display past events'),
      checked: past_events,
      onChange: value => {
        return setAttributes({
          start_date: '',
          end_date: '',
          past_events: value
        });
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
      label: "Event Grid View Layout",
      value: layout,
      options: [{
        label: 'Default',
        value: ''
      }, {
        label: 'Style 2',
        value: 'style2'
      }, {
        label: 'Style 3',
        value: 'style3'
      }, {
        label: 'Style 4',
        value: 'style4'
      }],
      onChange: value => setAttributes({
        layout: value
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
      label: "Order By",
      value: orderby,
      options: [{
        label: 'Event Start Date',
        value: 'event_start_date'
      }, {
        label: 'Event End Date',
        value: 'event_end_date'
      }, {
        label: 'Event Title',
        value: 'title'
      }],
      onChange: value => setAttributes({
        orderby: value
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RadioControl, {
      label: __('Order'),
      selected: order,
      options: [{
        label: __('Ascending'),
        value: 'ASC'
      }, {
        label: __('Descending'),
        value: 'DESC'
      }],
      onChange: value => setAttributes({
        order: value
      })
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelRow, {
      className: `wpea-start-date ${dateClassName}`
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, __('Event Start Date')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Dropdown, {
      label: __('Start Date'),
      position: "bottom left",
      contentClassName: "wpea-start-date__dialog",
      popoverProps: {
        placement: 'bottom-start'
      },
      renderToggle: _ref2 => {
        let {
          isOpen,
          onToggle
        } = _ref2;
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
          type: "button",
          className: "wpea-start-date__toggle",
          onClick: onToggle,
          "aria-expanded": isOpen,
          isLink: true
        }, eventDateLabel(start_date, true));
      },
      renderContent: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(DateTimePicker, {
        currentDate: start_date !== '' ? start_date : new Date(),
        onChange: value => setAttributes({
          start_date: value
        }),
        locale: settings.l10n.locale,
        is12Hour: is12HourTime,
        __nextRemoveHelpButton: true,
        __nextRemoveResetButton: true
      })
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelRow, {
      className: `wpea-end-date ${dateClassName}`
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, __('Event End Date')), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Dropdown, {
      label: __('End Date'),
      position: "bottom left",
      contentClassName: "wpea-end-date__dialog",
      popoverProps: {
        placement: 'bottom-start'
      },
      renderToggle: _ref3 => {
        let {
          isOpen,
          onToggle
        } = _ref3;
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
          type: "button",
          className: "wpea-end-date__toggle",
          onClick: onToggle,
          "aria-expanded": isOpen,
          isLink: true
        }, eventDateLabel(end_date));
      },
      renderContent: () => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(DateTimePicker, {
        currentDate: end_date !== '' ? end_date : new Date(),
        onChange: value => setAttributes({
          end_date: value
        }),
        locale: settings.l10n.locale,
        is12Hour: is12HourTime,
        __nextRemoveHelpButton: true,
        __nextRemoveResetButton: true
      })
    })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ServerSideRender, {
      block: "wpea-block/wp-events",
      attributes: attributes,
      key: JSON.stringify(attributes)
    }));
  },
  save: function () {
    // Rendering in PHP.
    return null;
  }
});
function eventDateLabel(date, start) {
  const settings = getSettings();
  const defaultLabel = start ? __('Select Start Date') : __('Select End Date');
  return date ? dateI18n(settings.formats.datetime, date) : defaultLabel;
}

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

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
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*************************!*\
  !*** ./blocks/index.js ***!
  \*************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wp_events__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./wp-events */ "./blocks/wp-events/index.js");
/**
 * Import blocks
 */

})();

/******/ })()
;
//# sourceMappingURL=gutenberg.blocks.js.map