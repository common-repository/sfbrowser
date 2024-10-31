<?php
/*
	Plugin Name:	SFBrowser for Wordpress
	Plugin URI:		http://code.google.com/p/sfbrowser/
	Version:		1.4.5
	SFBrowser Version: 3.3.2
	WordPress Version: 3.1.1
	Author:			Ron Valstar
	Author URI:		http://sjeiti.com/
	Author email:	sfb@sjeiti.com
	Description:	Incorporation of the SFBrowser into Wordpress. See http://sfbrowser.sjeiti.com/ for more info.
	It is the basic SFBrowser installation with some additional files:
		- wp_sfbrowser.php				wp plugin file
		- wp_jquery.wpadminsfb.js		javascript interface
	Plus of course a config file to suit Wordpress.
*/
if (!class_exists('WPSFBrowser')) {
	require_once 'wp_sjeiti.php';//plugin_dir_path(__FILE__).
	class WPSFBrowser extends WPSjeiti {
		//
		protected $sPluginName = 'WPSFBrowser';
		protected $sPluginId = 'sfbrowser';//strtolower($sPluginName);
		protected $sPluginHomeUri = 'http://sfbrowser.sjeiti.com/';
		protected $sPluginRootUri = '../wp-content/plugins/sfbrowser/';
		protected $sConstantId = 'SFB';
		protected $sVersion = '1.4.5';
		//
		protected $bOverrideMediaButtons = false;
		//
		// WPSFBrowser
		function __construct() {
			parent::__construct();
			//
			$aMediaButtons = $this->getValue('sfbrowser_overrideTinyMCE');
			foreach ($aMediaButtons as $nr=>$value) {
				if ($value=='on') {
					$this->bOverrideMediaButtons = true;
					break;
				}
			}
			$this->addHooks();
			//
			// 1.4 -> 1.4.1 error fix
			$sVal = get_option('sfbrowser_insertionRules');
			$sValue = str_replace(array("\n","\t"),"",$sVal);
			if ($sVal!=$sValue) update_option('sfbrowser_insertionRules', $sValue);
			//
		}
		//
		// admin_init
		function admin_init() {
			$sSection = 'default';
			$aForm = $this->getFormdata();
			foreach ($aForm as $sId=>$aField) {
				$sLabel = isset($aField['label'])?$aField['label']:'';
				$bHasType = isset($aField['type']);
				if ($bHasType&&$aField['type']=='label') {
					$sSection = $sId;
					add_settings_section($sSection, $sLabel, array(&$this,'section_text'), SFB_PAGE);
				} else if ($bHasType&&$aField['type']=='hidden') {
					$this->drawFormField($aField);
				} else {
					register_setting( SFB_SETTINGS, $sId, array(&$this,'options_sanatize') ); //TODO:validation
					add_settings_field( $sId, $sLabel, array(&$this,'drawFormField'), SFB_PAGE, $sSection, $aField);
				}
			}
		}
		//
		// admin_head
		function admin_head() {
			global $post;

			$this->remHooks();

			// are we a post or page?
			$sBaseUri = site_url();//get_bloginfo('url');
			$sUriPrefix = '';
			if (isset($post)&&isset($post->ID)) {
				$sPostId = $post->ID;
				$sPostUri = get_permalink($sPostId);
				$bIsPermalink = get_option('permalink_structure')!='';
				$sUriPrefix = $bIsPermalink?str_replace($sBaseUri,'',$sPostUri):'';
			}

			// check uploads folder and define it (so config will eat it)
			$sUp = $this->getValue('sfbrowser_uploadDirectory')."/";
			$sFld = "../".$sUp;
			if (!is_dir($sFld)) mkdir($sFld);
			define('WP_SFB_BASE','../../'.$sFld);

			if (is_writable($sFld)) { // check for subfolders for image, video, audio or media
				$oTmce = $this->getValue('sfbrowser_overrideTinyMCE');
				$i = 0;
				foreach (array("image","video","audio","media") as $sFolder) {
					if ($oTmce[$i]=="on") {
						$sSub = $sFld."/".$this->getValue('sfbrowser_'.$sFolder.'Directory');
						if (!is_dir($sSub)) mkdir($sSub);
					}
					$i++;
				}
			} else {
				$this->addError('Upload folder error. ','It seems that your uploads folder ('.$sFld.') is not write enabled. <a href="http://codex.wordpress.org/Changing_File_Permissions" target="wpsfberror">Please check this.</a>');
			}
			//
			// create plugins array
			$oPlug = $this->getValue('sfbrowser_plugins');
			$aPlug = $this->getObject('sfbrowser_plugins');
			$aPlugins = array();
			foreach ($oPlug as $i=>$s) $aPlugins[] = $aPlug['values'][$i];

			$oResize = $this->getValue('sfbrowser_resizeImages');

			echo N.T.T."<!-- wp-SFBrowser init ".(WP_SFB_DEBUG?'-debug mode- ':'')."-->".N;
			echo T.T.'<link rel="stylesheet" type="text/css" href="'.$this->sPluginRootUri.'wp_sfbrowser.css"></link>'.N;
			echo T.T.'<script type="text/javascript" src="'.$this->sPluginRootUri.'json2.min.js"></script>'.N;
			echo T.T.'<script type="text/javascript" src="'.$this->sPluginRootUri.'wp_jquery.sfbrowser.js"></script>'.N;
			echo T.T.'<script type="text/javascript">'.N;
			echo T.T.T.'var post_id = '.(isset($post->ID)?$post->ID:0).';'.N; // ugly global required for featured image
			echo T.T.T.'jQuery(function() {'.N;
			echo T.T.T.T.'jQuery.fn.wpadminsfb({'.N;
			echo T.T.T.T.T.' version: "'.$this->sVersion.'"'.N;
			echo T.T.T.T.T.',siteUri: "'.$sBaseUri.'"'.N;
			echo T.T.T.T.T.',filePrefixUri: "'.$sUriPrefix.'"'.N;
			echo T.T.T.T.T.',relativePath: '.($this->getValue('sfbrowser_relativePath')?'true':'false').N;
			echo T.T.T.T.T.',insertionRules: '.$this->getValue('sfbrowser_insertionRules').N;
			if (isset($post)) echo T.T.T.T.T.',featurenonce:\''.wp_create_nonce("set_post_thumbnail-".$post->ID).'\''.N; // $$ 1.4.3
			echo T.T.T.T.T.',override:	{'.N;
			echo T.T.T.T.T.T.'media:'.($this->getValue('sfbrowser_mediaMainMenu')?'true':'false').N;
			echo T.T.T.T.T.T.',tinymce_image:'.($oTmce[0]=='on'?'true':'false').N;
			echo T.T.T.T.T.T.',tinymce_video:'.($oTmce[1]=='on'?'true':'false').N;
			echo T.T.T.T.T.T.',tinymce_audio:'.($oTmce[2]=='on'?'true':'false').N;
			echo T.T.T.T.T.T.',tinymce_media:'.($oTmce[3]=='on'?'true':'false').N;
			echo T.T.T.T.T.T.',feature:'.($this->getValue('sfbrowser_featureImage')?'true':'false').N;
			echo T.T.T.T.T.'}'.N;
			if (count($oResize)>1) echo T.T.T.T.T.',resize: '.(($oResize[0]&&$oResize[1])?'['.$oResize[0].','.$oResize[1].']':'null').N; // $$ 1.4.3
			echo T.T.T.T.T.',imageFolder: \''.$this->getValue('sfbrowser_imageDirectory').'\''.N;
			echo T.T.T.T.T.',videoFolder: \''.$this->getValue('sfbrowser_videoDirectory').'\''.N;
			echo T.T.T.T.T.',audioFolder: \''.$this->getValue('sfbrowser_audioDirectory').'\''.N;
			echo T.T.T.T.T.',mediaFolder: \''.$this->getValue('sfbrowser_mediaDirectory').'\''.N;
			echo T.T.T.T.T.',sfbObject: {'.N;
			echo T.T.T.T.T.T.'cookie:'.($this->getValue('sfbrowser_cookie')?'true':'false').N;
			echo T.T.T.T.T.T.',dirs:'.($this->getValue('sfbrowser_dirs')?'true':'false').N;
			echo T.T.T.T.T.T.',swfupload:'.($this->getValue('sfbrowser_swfUpload')?'true':'false').N;
			echo T.T.T.T.T.T.',plugins:'.(count($aPlugins)?'[\''.implode('\',\'',$aPlugins).'\']':'[]').''.N;
			echo T.T.T.T.T.T.',debug:'.(WP_SFB_DEBUG?'true':'false').N;
			echo T.T.T.T.T.'}'.N;
			echo T.T.T.T.'});'.N;
			echo T.T.T.'});'.N;
			echo T.T.'</script>'.N;
			include_once($this->sPluginRootUri.'connectors/php/init.php');
			echo N.T.T."<!-- wp-SFBrowser end -->".N;
			
			// test warnings
			if (WP_SFB_LANG!=SFB_LANG&&WP_SFB_LANG!='') $this->addError("SFBrowser is not available in your set language '".WP_SFB_LANG."'."," It has reverted to '".SFB_LANG."'. If you care to translate it check the SFBrowser plugin directory for all the ''".SFB_LANG."'.po' files to duplicate and translate (there are several).");
			if (SFB_DEBUG) $this->addError(__('SFBrowser is running in debug mode.','sfbrowser'),__('Check the option at the bottom of this page to turn it off.','sfbrowser'));
		}
		//
		// admin_menu
		function admin_menu() {
			add_options_page(__('SFBrowser Management', 'sfbrowser'), __('SFBrowser', 'sfbrowser'), 'manage_options', 'sfbrowser', array(&$this,'settings_page'));
			if ($this->getValue('sfbrowser_mediaMainMenu')) {
				add_menu_page(__('SFBrowser', 'sfbrowser'), __('SFBrowser', 'sfbrowser'), 'edit_pages', 'sfbrowser_media', array(&$this,'settings_page'),'',10);
			}
		}
		//
		// media_buttons
		function media_buttons() {
			// contains a quick and dirty hack to remove buttons from Quickpress (since remove_action does not work there)
			$sReturn = '<span id="remMark" /><script type="text/javascript">var a=jQuery(\'#remMark\');while(a.prev().length)a.prev().remove();</script>';
			//
			$oMediaButtons = $this->getObject('sfbrowser_overrideTinyMCE');
			$aNames = $oMediaButtons['values'];
			$aValues =  $oMediaButtons['value'];
			$aIcons =  array('image'=>'image','audio'=>'music','video'=>'video','media'=>'other');
			foreach ($aNames as $i=>$sName) {
				$bOverride = $aValues[$i]=='on';
				$sIconSrc = 'images/media-button-'.$aIcons[$sName].'.gif?ver=20100531';
				$sTitle = __('Add '.$sName,'sfbrowser');
//				if ($bOverride)	$sReturn .= '<a href="javascript:jQuery.wpadminsfb.open(\''.$sName.'\')" title="'.$sTitle.'"><img src="'.$sIconSrc.'" alt="'.$sTitle.'" /></a>';
				if ($bOverride)	$sReturn .= '<a onclick="jQuery.wpadminsfb.open(\''.$sName.'\')" title="'.$sTitle.'"><img src="'.$sIconSrc.'" alt="'.$sTitle.'" /></a>';
				else			$sReturn .= _media_button($sTitle, $sIconSrc, $sName);
			}
			echo $sReturn;
			// original includes code
			/*$do_image = $do_audio = $do_video = true;
			if ( is_multisite() ) {
				$media_buttons = get_site_option( 'mu_media_buttons' );
				if ( empty($media_buttons['image']) ) $do_image = false;
				if ( empty($media_buttons['audio']) ) $do_audio = false;
				if ( empty($media_buttons['video']) ) $do_video = false;
			}
			$out = '';
			if ( $do_image ) $out .= _media_button(__('Add an Image'), 'images/media-button-image.gif?ver=20100531', 'image');
			if ( $do_video ) $out .= _media_button(__('Add Video'), 'images/media-button-video.gif?ver=20100531', 'video');
			if ( $do_audio ) $out .= _media_button(__('Add Audio'), 'images/media-button-music.gif?ver=20100531', 'audio');
			$out .= _media_button(__('Add Media'), 'images/media-button-other.gif?ver=20100531', 'media');
			$context = apply_filters('media_buttons_context', __('Upload/Insert %s'));
			printf($context, $out);*/
		}
		//////////////////////////////////////////
		//
		// addHooks
		function addHooks(){
			add_action('admin_print_scripts',array(&$this,'admin_print_scripts'));
			add_action('admin_init',array(&$this,'admin_init'));
			add_action('admin_head',array(&$this,'admin_head'));
			add_action('admin_menu',array(&$this,'admin_menu'));
			if ($this->bOverrideMediaButtons) add_action('media_buttons',array($this,'media_buttons'),20);
			add_filter('plugin_action_links',array(&$this,'plugin_action_links'),10,2);
		}
		//
		// remHooks
		function remHooks(){
			if ($this->bOverrideMediaButtons) remove_action('media_buttons','media_buttons');
		}
		//
		// options_sanatize
		function options_sanatize($a){
			return $a;
		}
		//
		// settings_page
		function settings_page() {
			?><style>
				.postbox .inside {padding: 0 15px 5px 15px;}
				.postbox .inside form {text-align:center;margin:5px 0;}
				.wp-sfb-settings .main h3 {margin-top:30px;}
				.wp-sfb-settings .main p {margin-left:10px;}
				.wp-sfb-settings .main ul.nolist {margin-left:10px;}
			</style><?php
			//
			//echo ' WP_SFB_DEBUG:'.(WP_SFB_DEBUG?1:0) ; //##
			//echo ' SFB_DEBUG'.(SFB_DEBUG?1:0); //##
			//	
			echo T.'<div class="wrap wp-sfb-settings">';
			echo T.T.'<div id="icon-options-general" class="icon32"><br/></div>';

			echo T.T.'<h2>'.__('SFBrowser options','sfbrowser').'</h2>';

			// debug alerts
			$this->showErrors();

			// start form
			echo T.T.'<div class="postbox-container main" style="width:65%;"><div class="metabox-holder"><div class="meta-box-sortables ui-sortable">';
			
			echo T.T.'<p style="max-width:700px;">'.__('_SFBrowser explanation','sfbrowser').'</p>';
			echo T.T.T.'<form method="post" action="options.php">';
			settings_fields(SFB_SETTINGS);
			do_settings_sections(SFB_PAGE);
			echo T.T.T.T.'<p><br/><input type="submit" name="submit" class="button-primary" value="'.__('Save changes','sfbrowser').'" /></p>';
			echo T.T.T.'</form>';
			echo T.T.'</div></div></div>';
			
			// side
			echo T.T.'<div class="postbox-container side" style="width:20%;"><div class="metabox-holder"><div class="meta-box-sortables ui-sortable">';
			$this->plugin_like('SFBrowser','http://flattr.com/thing/99947/SFBrowser');
			echo T.T.'</div></div></div>';

			echo T.'</div>';
		}
		//
		// admin_print_scripts
		function admin_print_scripts() {
//			wp_enqueue_script('dashboard'); // for postbox // disabled :: causes 'delete plugin' not working
			if ($this->getValue('sfbrowser_featureImage')=='on') wp_enqueue_script('set-post-thumbnail');
			//wp_enqueue_script('postbox');
			//wp_enqueue_script('thickbox');
			//wp_enqueue_script('media-upload');
		}
		//
		// override::getFormdata
		function getFormdata() {
			if (isset($this->aForm)) return $this->aForm;
			//
			@load_plugin_textdomain('sfbrowser','/wp-content/plugins/sfbrowser/wp_lang');
			$aForm = array(

				 'label1'=>array('label'=>__('Basic settings','sfbrowser'),'type'=>'label')
				,'sfbrowser_relativePath'=>array(		'default'=>'on',		'label'=>__('Use relative paths','sfbrowser'),		'type'=>'checkbox')
				,'sfbrowser_uploadDirectory'=>array(	'default'=>get_option('upload_path'),	'label'=>__('Upload directory','sfbrowser'),	'w'=>'30')
				,'sfbrowser_imageDirectory'=>array(		'default'=>'',			'label'=>__('Image directory','sfbrowser'), 'text'=>__('(relative to upload directory)','sfbrowser'),	'w'=>'10')
				,'sfbrowser_videoDirectory'=>array(		'default'=>'',			'label'=>__('Video directory','sfbrowser'), 'text'=>__('(relative to upload directory)','sfbrowser'),	'w'=>'10')
				,'sfbrowser_audioDirectory'=>array(		'default'=>'',			'label'=>__('Audio directory','sfbrowser'), 'text'=>__('(relative to upload directory)','sfbrowser'),	'w'=>'10')
				,'sfbrowser_mediaDirectory'=>array(		'default'=>'',			'label'=>__('Media directory','sfbrowser'), 'text'=>__('(relative to upload directory)','sfbrowser'),	'w'=>'10')

				,'label2'=>array('label'=>__('Override Wordpress elements','sfbrowser'),'type'=>'label',	'text'=>__('_sfbrowser override explanation.','sfbrowser'))
				,'sfbrowser_mediaMainMenu'=>array(		'default'=>'on',					'label'=>__('Media menu item','sfbrowser'),			'type'=>'checkbox')

//				,'sfbrowser_overrideTinyMCE'=>array(	'default'=>'a:4:{i:0;s:2:"on";i:1;s:2:"on";i:2;s:2:"on";i:3;s:2:"on";}',						'label'=>__('TinyMCE Upload/Insert','sfbrowser'),	'type'=>'checkbox', 'values'=>array(
				,'sfbrowser_overrideTinyMCE'=>array(	'default'=>array('on','on','on','on'),	'label'=>__('TinyMCE Upload/Insert','sfbrowser'),	'type'=>'checkbox', 'values'=>array(
					 __("image",'sfbrowser')
					,__("video",'sfbrowser')
					,__("audio",'sfbrowser')
					,__("media",'sfbrowser')
				))
				,'sfbrowser_featureImage'=>array(		'default'=>'on',		'label'=>__('Feature image','sfbrowser'),			'type'=>'checkbox')

				,'label3'=>array('label'=>__('Insertion rules','sfbrowser'),	'type'=>'label', 'text'=>__('_insertion rules explanation.','sfbrowser'))
				,'sfbrowser_insertionRules'=>array(		
					'type'=>'insertion',		
					'default'=>'{"rule_0":{"e":"jpg,jpeg,gif,png","s":"<img src=\"%file\" alt=\"%name\" />","m":""},"rule_1":{"e":"mp4,m4v,ogv","s":"<video width=\"320\" height=\"240\" src=\"%file\" />","m":"<videowidth=\"320\" height=\"240\">|<source src=\"%file\" type=\"video/%mime\" />|</video>"},"rule_2":{"e":"pdf,doc","s":"<a href=\"%file\">%name</a> (%size)","m":"<ul>|<li><a href=\"d.php?d=%file\">%name</a> (%size)</li>|</ul>"}}'
				)

				,'label4'=>array('label'=>__('SFBrowser settings','sfbrowser'),	'type'=>'label',		'text'=>__('_sfbrowser settings explanation.','sfbrowser'))
//				,'sfbrowser_plugins'=>array(			'default'=>'a:2:{i:0;s:2:"on";i:3;s:2:"on";}',	'label'=>__('Plugins','sfbrowser'),					'type'=>'checkbox', 'values'=>array(
				,'sfbrowser_plugins'=>array(			'default'=>array(0=>'on',3=>'on'),	'label'=>__('Plugins','sfbrowser'),					'type'=>'checkbox', 'values'=>array(
					 "imageresize"
					,"filetree"
					,"createascii"
					,"wp_db"
				))
				,'sfbrowser_resizeImages'=>array(		'default'=>array(999,999),			'label'=>__('Resize images','sfbrowser'),			'values'=>array("w","h"),	'text'=>__('_resizeExplain','sfbrowser'),	'w'=>'4')
				,'sfbrowser_cookie'=>array(				'default'=>'',			'label'=>__('Save cookie','sfbrowser'),				'type'=>'checkbox',			'text'=>__('_cookieExplain','sfbrowser'))
				,'sfbrowser_dirs'=>array(				'default'=>'on',		'label'=>__('Directory creation','sfbrowser'),		'type'=>'checkbox',			'text'=>__('_dirsExplain','sfbrowser'))
				,'sfbrowser_swfUpload'=>array(			'default'=>'on',		'label'=>__('Swf upload','sfbrowser'),				'type'=>'checkbox',			'text'=>__('_swfUploadExplain','sfbrowser'))
				,'sfbrowser_debug'=>array(				'default'=>'',			'label'=>__('Debug mode','sfbrowser'),				'type'=>'checkbox',			'text'=>__('_debugExplain','sfbrowser'))
			);
			$this->aForm = $this->setDefaultOptions($aForm);
			return $this->aForm;
		}
		//
		// override::drawFormField 
		function drawFormField($data) {
			$sId = $data['id'];
			$sLabel = isset($data['label'])?$data['label']:'';
			$bRequired = isset($data['req'])?$data['req']:false;
			$sRequired = $bRequired?' required="required"':'';
			$sType = isset($data['type'])?$data['type']:'text';
			$sValue = $data['value'];
			$sValTr = ' value="'.$sValue.'"';
			$aValues = isset($data['values'])?$data['values']:array();//$sId=>$sLabel
			$sWidth = isset($data['w'])?' size="'.$data['w'].'" ':'';
			switch ($sType) {
				case 'insertion': // test
					$sNewInsert  = '<p class="insertionRule">';
					$sNewInsert .= '<input size="10" class="sfbir_e" value="" type="text" onchange="jQuery.wpadminsfb.buildInsertionRule(\''.$sId.'\');" />';
					$sNewInsert .= '<input size="40" class="sfbir_s" value="" type="text" onchange="jQuery.wpadminsfb.buildInsertionRule(\''.$sId.'\');" />';
					$sNewInsert .= '<input size="40" class="sfbir_m" value="" type="text" onchange="jQuery.wpadminsfb.buildInsertionRule(\''.$sId.'\');" />';
					$sNewInsert .= ' &nbsp; <a onclick="jQuery.wpadminsfb.remInsertionRule(this,\''.$sId.'\');">'.__('Remove rule','sfbrowser').'</a>';
					$sNewInsert .= '</p>';
					if (isset($data['text'])) echo '<span class="description">'.$data['text'].'</span><br/>';
					echo '</td></tr><tr style="padding-top:0;"><td style="padding-top:0;margin-top:0;" colspan="2" valign="top">';
					echo '<strong style="display:inline-block;min-width:196px;">'.__('Extensions','sfbrowser').'</strong><strong style="display:inline-block;min-width:279px;">'.__('Rule for single file','sfbrowser').'</strong><strong>'.__('Rule for list of files','sfbrowser').'</strong>';
					echo '<input name="'.$sId.'" id="'.$sId.'" type="hidden" value="'.htmlspecialchars($sValue).'" />';
					$aRules = json_decode($sValue);
					if ($aRules) {
						//echo '<pre>'.print_r($aRules).'</pre>';
						//echo '<pre>'.(is_array($aRules)?'t':'f').'</pre>';
						foreach ($aRules as $sName=>$aRule) {
							$aExt = $aRule->e; // extensions
							$aSng = $aRule->s; // single rule
							$aMlt = $aRule->m; // multiple rule
							echo str_replace(array(
								//'sfbir'
								 'e" value=""'
								,'s" value=""'
								,'m" value=""'
							),array(
								//$sName
								 'e" value="'.htmlspecialchars($aExt).'"'
								,'s" value="'.htmlspecialchars($aSng).'"'
								,'m" value="'.htmlspecialchars($aMlt).'"'
								//,'e" value="'.implode(",",$aExt).'"'
								//,'s" value="'.implode(",",$aSng).'"'
								//,'m" value="'.implode(",",$aMlt).'"'
							),$sNewInsert);
						}
					}
					echo '<input id="insertionAdd" type="hidden" value="'.htmlspecialchars($sNewInsert).'" />';
					echo '<a onclick="jQuery.wpadminsfb.addInsertionRule(this)">'.__('Add new rule','sfbrowser').'</a><p>&nbsp;</p>';
				break;
				default: 
					parent::drawFormField($data);
			}
		}
		//
		// override::tbvFeature 
		function tbvFeature($data) {
		}
/*
post.php (182,1):  function update_attached_file( $attachment_id, $file ) {
post.php (706,1):  function get_post_type( $the_post = false ) {
post.php (733,1):  function get_post_type_object( $post_type ) {
post.php (757,1):  function get_post_types( $args = array(), $output = 'names', $operator = 'and' ) {
post.php (1598,1): function wp_count_attachments( $mime_type = '' ) {
post.php (1627,1): function wp_match_mime_types($wildcard_mime_types, $real_mime_types) {
post.php (1660,1): function wp_post_mime_type_where($post_mime_types, $table_alias = '') {

post.php (2149,1): function wp_insert_post($postarr = array(), $wp_error = false) {
post.php (2406,1): function wp_update_post($postarr = array()) {

post.php (3237,1): function is_local_attachment($url) {
post.php (3291,1): function wp_insert_attachment($object, $file = false, $parent = 0) {
post.php (3442,1): function wp_delete_attachment( $post_id, $force_delete = false ) {
post.php (3537,1): function wp_get_attachment_metadata( $post_id = 0, $unfiltered = false ) {
post.php (3559,1): function wp_update_attachment_metadata( $post_id, $data ) {
post.php (3577,1): function wp_get_attachment_url( $post_id = 0 ) {
post.php (3611,1): function wp_get_attachment_thumb_file( $post_id = 0 ) {
post.php (3633,1): function wp_get_attachment_thumb_url( $post_id = 0 ) {
post.php (3660,1): function wp_attachment_is_image( $post_id = 0 ) {
post.php (3685,1): function wp_mime_type_icon( $mime = 0 ) {




WP_INSERT_ATTACHMENT
	$wp_filetype = wp_check_filetype(basename($filename), null );
	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
		'post_content' => '',
		'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $filename, 37 );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id,  $attach_data );


*/
	}
}
global $wpsfbrowser;
$wpsfbrowser = new WPSFBrowser();
?>