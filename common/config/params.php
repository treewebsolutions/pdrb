<?php
return [
	// This parameter is used to remove a specific URL segment in order to get the right uploads URL
	'upload.ignoredBasePaths' => ['admin', 'dashboard'],
    'image.extensions' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'],
    'image.mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
    // Not begin with dot and allow only specific characters
    'sanitize.pattern' => '/^(?!^\.)[a-zA-Z 0-9\/\.\,\:\+\-\_\@\[\]\(\)\{\}\?\!\&\'(\r\n|\r|\n)]*$/i',
    'sanitize.allowedHtmlTags' => ['strong', 'em', 'ul', 'ol', 'li', 'br', 'hr', 'p', 'div', 'span'],
    'sanitize.allowedHtmlAttributes' => ['class', 'id', 'style', 'data-', 'aria-'],
];
