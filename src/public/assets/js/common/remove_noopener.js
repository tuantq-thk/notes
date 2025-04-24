$(function () {
	// collect a tag with target="_blank" and has class no-noopener
	var aTagsNoopener = document.querySelectorAll('a[target="_blank"].no-noopener');
	// remove noopener attribute
	aTagsNoopener.forEach((aTag) => aTag.removeAttribute("rel"));
});
 