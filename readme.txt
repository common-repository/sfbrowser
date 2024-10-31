=== SFBrowser ===
Contributors: Sjeiti
Tags: filebrowser, file browser, media, upload, uploader, file, sfbrowser, image, resize, ascii, preview, directory, folder, rename, move
Requires at least: 3.1
Tested up to: 3.1
Stable tag: 1.4.5

SFBrowser is a file browser and uploader. It can replace the existing media library.


== Description ==

SFBrowser is a file browser and uploader. It can be used as an alternative for the existing media library. But it can also quite easily be used to support other plugins and/or extensions.
Unlike the existing media library SFBrowser does not automatically store references to files in the database. So files uploaded through FTP are immediately visible.

= how it works =
You can set this plugin to override specific parts of the admin interface: mainly those parts that deal with the media library or parts that use an upload form.
For the wysiwyg editor you can define how to insert different filetypes. These rules can be enhanced with the metadata of the file (like filesize, filetime or dimensions).

= SFBrowser features =
* ajax file upload
* optional as3 swf upload (queued multiple uploads, upload progress, upload canceling, selection filtering, size filtering)
* localisation (English, Dutch or Spanish)
* server side script connector
* plugin environment (with imageresize plugin, filetree and create/edit ascii)
* data caching (minimal server communication)
* sortable file table
* file filtering
* file renameing
* file duplication
* file movement
* file download
* file path copy (CTRL-C)
* file/folder context menu
* file preview (image, audio, video, zip, text/ascii, pdf and swf)
* folder creation
* multiple files selection (not in IE for now)
* inline or overlay window
* window dragging and resizing
* cookie for size, position and path
* keyboard shortcuts
* key file selection

= using sfbrowser in your own plugin or function =
Using SFBrowser in your own plugin is actually quite easy. The only thing you need is a (hidden) input field, a button that calls up the SFBrowser, and some javascript.
To call up SFBrowser you use this function:
	jQuery.sfb({
		 select: myCallbackFunction
	});
There are [more variables you can parse](http://sfbrowser.sjeiti.com/#javascript) but the 'select' is the important one.
The callback function can look like this:
	var myCallbackFunction = function myCallbackFunction(files) {
		var oFile = files[0];
		jQuery('input#selectedFile').val(oFile.file);
		jQuery('label#fileLabel').html(oFile.file+" "+oFile.size);
	}
You can see from this function that the parsed variable is a list of objects. The propeties of those objects are [described here](http://sfbrowser.sjeiti.com/#select).

= Ideas =

If you have any ideas on how to improve this plugin [post a message here](http://wordpress.org/tags/sfbrowser?forum_id=10).

Some improvements that are on the todo list are:

* context menu item that copies the filepath to clipboard
* background- and header image override
* image manipulation plugin

== Installation ==

1. After download place the files in the Wordpress plugin directory (wp-content/plugins/sfbrowser/)
1. Activate the plugin through the 'Plugins' menu in WordPress
1. In the admin settings menu you can adjust SFBrowser according to you needs.

== Screenshots ==

1. The right-click context menu
2. Simultaneous uploads
3. File renaming
4. File movement by drag and drop
5. The image resize plugin
6. Video preview