��    
      l      �       �      �                %     2     P      _      �     �  �  �  �  c  -   �  [   (  7   �  �  �  D   �
  e   �
  t   X  G   �        
                 	                    _SFBrowser explanation _cookieExplain _debugExplain _dirsExplain _insertion rules explanation. _resizeExplain _sfbrowser override explanation. _sfbrowser settings explanation. _swfUploadExplain Project-Id-Version: wp-sfbrowser
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2010-11-21 13:43+0100
PO-Revision-Date: 2010-11-21 13:43+0100
Last-Translator: Ron Valstar <poedit@ronvalstar.nl>
Language-Team: 
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Poedit-KeywordsList: __;_e
X-Poedit-Basepath: .
X-Poedit-Language: English
X-Poedit-Country: UNITED KINGDOM
X-Poedit-SearchPath-0: ..
 SFBrowser is a file browser and uploader. It can be used as an alternative for the existing but annoying media library. But it can also quite easily be used to support other plugins and/or extensions.<br/>Unlike the existing media library SFBrowser does not store references to files in the database. Plus it returns the relative paths to files (as opposed to the absolute paths used by the media library). (cookie saves size and position of SFBrowser) (debug mode allows writing to log files (if writable), window console log and Flash traces) (allows creation, renaming and deletion of directories) <p>These are rules that define the way certain file types are inserted into your editor. The first field should be a comma separated list of file extensions. The second field is the rule for a single file. The third is an optional field for a list of files (if left blank the single rule is used). There are several variables you can use in your rule:</p><ul class="nolist"><li>%file: uri to the file</li><li>%mime: file extension</li><li>%rsize: size in bytes</li><li>%size: size in appropiate format (kB, MB, GB etc...)</li><li>%time: time in milliseconds from Unix epoch</li><li>%date: formatted date/time</li><li>%width: width if present</li><li>%height: height if present</li></ul><p>For instance rule for an image will look like this <span class="wpsfbrule">&lt;img src="%file" alt="%name" /&gt;</span>. The third rule can be made up of three parts delimited by pipes: a beginning, a middle that is iterated over the number of files, and an end. If it consists only of two parts there will be no end, if only one part is present it will be considered the iterative part (i.e. similar to the single file rule). For example an unordered list: <span class="wpsfbrule">&lt;ul&gt;|&lt;li&gt;&lt;a href="%file"&gt;%name&lt;/a&gt;&lt;/li&gt;|&lt;/ul&gt;</span>.</p> (works only for tinymce image, otherwise use the imageresize plugin) Individual Wordpress elements that use the existing media library can be overridden to use SFBrowser. SFBrowser comes with a great number of native settings. These are only a few, more will be added in future releases. (uses Flash for upload so you can upload multiple files simultaniously) 