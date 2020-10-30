(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[1],{

/***/ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./resources/js/components/Chat/ControlsComponent/style.css":
/*!**************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/postcss-loader/src??ref--6-2!./resources/js/components/Chat/ControlsComponent/style.css ***!
  \**************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "#control-panel-container {\n    position: fixed;\n    z-index:2147483630 !important;\n    right:0px;\n    width:4em;\n}\n\nbutton.btn {\n    right:10px;\n    border-radius: 2em !important;\n    width: 4em;\n    height: 4em;\n    margin-top:0.5em;\n}\n\n/**Adjust the slash since font awesome doesn't offer a video slash option */\n#btn-local-screenshare-toggle >>> .fa-slash {\n    display:block;\n    margin-top:-20px;\n}\n\n.btn-off {\n    opacity: 0.75;\n}\n\n/*\nWhen in Fullscreen\n */\n#control-panel-container.fullscreen {\n    margin-top:0px !important;\n    top:0px;\n    right:0px !important;\n}", ""]);

// exports


/***/ }),

/***/ "./resources/js/components/Chat/ControlsComponent/style.css":
/*!******************************************************************!*\
  !*** ./resources/js/components/Chat/ControlsComponent/style.css ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../../../node_modules/css-loader??ref--6-1!../../../../../node_modules/postcss-loader/src??ref--6-2!./style.css */ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./resources/js/components/Chat/ControlsComponent/style.css");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ })

}]);