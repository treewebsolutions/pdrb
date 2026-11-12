/**
 * Fix for jQuery passive event listener warning
 * Makes touchstart, touchmove, wheel, and mousewheel events passive by default
 */
(function() {
    // Test if browser supports passive event listeners
    var supportsPassive = false;
    try {
        var opts = Object.defineProperty({}, 'passive', {
            get: function() {
                supportsPassive = true;
            }
        });
        window.addEventListener('testPassive', null, opts);
        window.removeEventListener('testPassive', null, opts);
    } catch (e) {}

    if (supportsPassive) {
        // Patch native addEventListener
        var addEvent = EventTarget.prototype.addEventListener;
        EventTarget.prototype.addEventListener = function(type, listener, options) {
            var usesListenerOptions = typeof options === 'object' && options !== null;
            var useCapture = usesListenerOptions ? options.capture : options;

            options = usesListenerOptions ? options : {};
            
            // Make these events passive by default unless explicitly set to false
            if (type === 'touchstart' || type === 'touchmove' || type === 'wheel' || type === 'mousewheel') {
                if (options.passive === undefined) {
                    options.passive = true;
                }
            }
            
            options.capture = useCapture !== undefined ? useCapture : false;
            addEvent.call(this, type, listener, options);
        };

        // Patch jQuery's event system when jQuery is available
        if (typeof jQuery !== 'undefined') {
            patchJQuery(jQuery);
        } else {
            // Wait for jQuery to load
            var checkJQuery = setInterval(function() {
                if (typeof jQuery !== 'undefined') {
                    clearInterval(checkJQuery);
                    patchJQuery(jQuery);
                }
            }, 10);
        }
    }

    function patchJQuery($) {
        // Patch jQuery's on() method
        var originalOn = $.fn.on;
        $.fn.on = function(types) {
            if (typeof types === 'string') {
                var typeArray = types.split(' ');
                var hasPassiveEvent = false;
                
                for (var i = 0; i < typeArray.length; i++) {
                    var type = typeArray[i];
                    if (type === 'touchstart' || type === 'touchmove' || type === 'wheel' || type === 'mousewheel') {
                        hasPassiveEvent = true;
                        break;
                    }
                }
                
                // Add passive option to the arguments if it's a passive event type
                if (hasPassiveEvent && arguments.length > 0) {
                    var lastArg = arguments[arguments.length - 1];
                    if (typeof lastArg === 'object' && lastArg !== null && !Array.isArray(lastArg)) {
                        if (lastArg.passive === undefined) {
                            lastArg.passive = true;
                        }
                    }
                }
            }
            
            return originalOn.apply(this, arguments);
        };

        // Patch jQuery's bind() method
        var originalBind = $.fn.bind;
        if (originalBind) {
            $.fn.bind = function(types) {
                if (typeof types === 'string') {
                    if (types === 'touchstart' || types === 'touchmove' || types === 'wheel' || types === 'mousewheel') {
                        // Try to make it passive
                        var args = Array.prototype.slice.call(arguments);
                        return originalOn.call(this, types, {passive: true}, args[1]);
                    }
                }
                return originalBind.apply(this, arguments);
            };
        }
    }
})();

