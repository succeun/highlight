<?php

namespace Plugins\Highlight;

use \Typemill\Plugin;

class Highlight extends Plugin
{
	protected $settings;
	
    public static function getSubscribedEvents()
    {
		return array(
			'onSettingsLoaded'		=> 'onSettingsLoaded',		
			'onTwigLoaded' 			=> 'onTwigLoaded'
		);
    }
	
	public function onSettingsLoaded($settings)
	{
		$this->settings = $settings->getData();
	}
	
	
	public function onTwigLoaded()
	{
		$highlightSettings = $this->settings['settings']['plugins']['highlight'];
		
		/* add external CSS and JavaScript */
		$this->addCSS('/highlight/public/reset.css');	// For theme code css reset
		if (isset($highlightSettings['theme'])) {
			$this->addCSS('/highlight/public/'.$highlightSettings['theme'].'.css');
		} else {
			$this->addCSS('/highlight/public/default.css');
		}
		
		$this->addJS('/highlight/public/highlight.pack.js');
		$this->addJS('/highlight/public/highlightjs-line-numbers.min.js');
		$this->addJS('/highlight/public/highlightjs-highlight-lines.js');
		$this->addJS('/highlight/public/highlightjs-highlight-filename.js');
		
		/* initialize the script */
		$isSingleLine = false;	// whether show line number in single line or not
		if (isset($highlightSettings['singleLine']) && $highlightSettings['singleLine'] == 'true') {
			$isSingleLine = true;
		}
	
		$this->addInlineJS('
			document.addEventListener("DOMContentLoaded", function() {
				document.querySelectorAll("code[class*=\'language-\']").forEach(function(element, index) {
					var attrOpt = element.attributes["data-options"];
					var options = {};
					if (attrOpt) {
						options = eval("(" + attrOpt.value + ")");	// JSON
					}
					options.singleLine = '.$isSingleLine.';
					element.highlightOptions = options;
				});
			});
		');
		
		$this->addInlineJS('hljs.initHighlightingOnLoad();');
		
		$isLineNumbers = false;	// whether show line number or not
		if (isset($highlightSettings['lineNumber']) && $highlightSettings['lineNumber'] == 'true') {
			// line Numbers options: singleLine, startFrom
			$this->addInlineJS('
				window.addEventListener("DOMContentLoaded", function() {
					document.querySelectorAll("code.hljs").forEach(function(element, index) {
						hljs.lineNumbersBlock(element, element.highlightOptions);	// Show line number.
					});
				});
			');
			$isLineNumbers = true;
		}
		
		// highlight Lines options: highlightLines, highlightColor
		// { ... highlightLines: [2,3,4], highlightColor: '#ccc' }
		// { ... highlightLines: [2,3,4], highlightColor: 'rgba(255, 255, 255, 0.2)' }
		//
		// Show filename optinos: filename, filenameBackgroundColor, filenameColor, filenameAlgin (left|right)
		$this->addInlineJS('
			window.addEventListener("DOMContentLoaded", function() {
				document.querySelectorAll("code.hljs").forEach(function(element, index) {
					if (element.highlightOptions) {
						var options = toHighlightLinesOptions(element.highlightOptions.startFrom, element.highlightOptions.highlightLines, element.highlightOptions.highlightColor); 
						hljs.highlightLinesCode(element, options, '.$isLineNumbers.');	// Show line highlight.
						
						// Show filename
						if (element.highlightOptions.filename) {
							hljs.highlightFilenameCode(element, {
								filename: element.highlightOptions.filename,
								backgroundColor: element.highlightOptions.filenameBackgroundColor || "#888",
								color: element.highlightOptions.filenameColor || "white",
								align: element.highlightOptions.filenameAlgin || "left",
							});
						}
					}
				});
				
				function toHighlightLinesOptions(startFrom, lines, color) {
					startFrom = (startFrom == null || startFrom == "") ? 1 : startFrom;
					color = color || "rgba(255, 255, 0, 0.5)";
					var opts = [];
				  if (lines) {
					for (var i = 0; i < lines.length; i++) {
					  opts.push({
						start: lines[i] - startFrom,
						end: lines[i] - startFrom,
						color: color
						});
					}
				  }
				  return opts;
				}
			});
		');
	}
}