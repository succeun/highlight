(function(w, d) {
    'use strict';

    if(w.hljs) {
        w.hljs.initHighlightFilenameOnLoad = initHighlightFilenameOnLoad;
        w.hljs.highlightFilenameCode = highlightFilenameCode;
    }

    function initHighlightFilenameOnLoad(options) {
        function callHighlightFilenameCode() {
            var codes = d.getElementsByClassName('hljs');
            for(var i = 0; i < codes.length; ++i) {
                highlightFilenameCode(codes[i], options[i]);
            }
        }

        if(d.readyState !== 'loading') {
            callHighlightFilenameCode();
        }
        else {
            w.addEventListener('DOMContentLoaded', function() {
                callHighlightFilenameCode();
            });
        }
    }

    function highlightFilenameCode(code, option) {
        if(option === null || Object.keys(option).length == 0) {
            return;
        }

        code.style.paddingTop = '2.2em';
        code.style.position = 'relative';

        var filename = d.createElement('div');
        filename.style.position = 'absolute';
        filename.style.top = '0em';
		// Fix adding align (left | right)
		if (option.align && option.align == 'right') {
			filename.style.right = '2em';
		} else {
			filename.style.left = '2em';
		}
        filename.style.backgroundColor = option.backgroundColor || '#888';
        filename.style.color = option.color || 'white';
        filename.style.padding = '0.2em';
        filename.textContent = option.filename;

        code.insertBefore(filename, code.firstChild);
    }
}(window, document));