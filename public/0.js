(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[0],{

/***/ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./resources/js/components/Chat/ChatComponent/style.css":
/*!**********************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/postcss-loader/src??ref--6-2!./resources/js/components/Chat/ChatComponent/style.css ***!
  \**********************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, ".btn {\n    border-radius: 0px;\n}\n\n/*Place this behind the controls and local video but infront of everything else */\n.peer-video-rebinding-wait {\n    z-index:2147483620;\n    background:#000;\n    color:#fff;\n    position: fixed;\n    top: 0;\n    bottom: 0;\n    left: 0;\n    right: 0;\n}\n.peer-video-rebinding-wait >>> h1 {\n    margin-top:50vh;\n}\n#message-box {\n    border-radius:0px;\n}\n/*Place this infront of the rebinding thing*/\n.btn-show-messages {\n    z-index:2147483621;\n}\n.no-video-connections {\n    padding: 4vh;\n    text-align: center;\n}\n.chat-disabled >>> input {\n    opacity:.5;\n}\n.chat-disabled >>> button {\n    opacity:.5;\n}\n.btn-leave-chat {\n    position: absolute;\n    width: 25%;\n    top: 0px;\n    left: 50%;\n    margin-top: 6px;\n    margin-left: -12.5%;\n}\n.video-connections {\n    background: #eee;\n    color:#555;\n    padding: 1vh;\n    border-radius: 5px;\n    box-shadow: 0px 1px 3px #ccc;\n}\n.no-video-connections >>> h1 {\n    height:2em;\n}\n.no-video-connections >>> i {\n    position: absolute;\n    /*Center the icons*/\n    left: 0;\n    right: 0;\n}\n\n.btn-off {\n    opacity: 0.75;\n}\n\n\n/*Remove any previous positions*/\n.is-draggable {\n    top:unset;\n    bottom: unset;\n    right:unset;\n    left:unset;\n}\n\nvideo {\n    border-radius: 5px;\n    box-shadow: 0px 1px 3px #000;\n}\nvideo.peer-video-fullscreen {\n    box-shadow: none;\n}\n\n/**Adjust the slash since font awesome doesn't offer a video slash option */\n#btn-local-screenshare-toggle >>> .fa-slash {\n    display:block;\n    margin-top:-20px;\n}\n\n#local-video-container.local-video-sm,\n#local-video-container.local-video-sm >>> video {\n    margin-right:25px;\n    width:100px;\n}\n#local-video-container.local-video-md,\n#local-video-container.local-video-md >>> video {\n    width:200px;\n}\n#local-video-container.local-video-lg,\n#local-video-container.local-video-lg >>> video {\n    width:300px;\n}\n\n.progress-bar-vertical {\n    width: 20px;\n    display: flex;\n    align-items: flex-end;\n}\n\n.progress-bar-vertical .progress-bar {\n    width: 100%;\n    height: 0;\n    transition: height 0.6s ease;\n}\n\n\n#local-video-volume-meter {\n    width: 5px;\n    height: 95%;\n    display: flex;\n    align-items: flex-end;\n    position: absolute;\n    right: -1px;\n    bottom: 5%;\n    opacity: 0.75;\n}\n\n/* Initial position */\n#local-video-container {\n    margin-top:20px;\n    position:fixed;\n    right:5em;\n    border-radius:3px;\n    z-index: 2147483638;\n}\n\n/* When fullscreened, shift things around*/\n.chat-container.peer-video-fullscreen {\n    height:0px !important;\n}\n\n#local-video-container.local-video-overlay,\n#local-video-container.local-video-overlay >>> video {\n    margin-right:0px;\n    bottom:0px;\n    right:0px;\n}\n\n.message-box.peer-video-fullscreen {\n    z-index:2147483622;\n}\n\n/*Videos container shrink so messages and stuff shows correctly*/\n#peer-videos-container.peer-video-fullscreen >>> .video-connections {\n    height:0px;\n}\n\n/* Main Video Fullscreen */\n#user-prompt {\n    margin-top:10%;\n}\n.fade-enter-active, .fade-leave-active {\n    transition: opacity .5s;\n}\n.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {\n    opacity: 0;\n}", ""]);

// exports


/***/ }),

/***/ "./resources/js/components/Chat/ChatComponent/style.css":
/*!**************************************************************!*\
  !*** ./resources/js/components/Chat/ChatComponent/style.css ***!
  \**************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../../../node_modules/css-loader??ref--6-1!../../../../../node_modules/postcss-loader/src??ref--6-2!./style.css */ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./resources/js/components/Chat/ChatComponent/style.css");

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