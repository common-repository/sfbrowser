<?php
if (!class_exists('WPSjeiti')) {
	class WPSjeiti {
		//
		protected $sPluginName;
		protected $sPluginId;
		protected $sPluginHomeUri;
		protected $sPluginRootUri;
		protected $sConstantId;
		protected $sVersion;
		protected $aForm;
		//
		private $aError = array();
		//
		// WPSFBrowser
		function __construct() {
			// init vars
			$sDebugName = 'WP_'.$this->sConstantId.'_DEBUG';
			define($sDebugName,						$this->getValue($this->sPluginId.'_debug'));
			define('WP_'.$this->sConstantId.'_LANG',		str_replace('-','_',get_bloginfo('language')));
			define($this->sConstantId.'_SETTINGS',	$this->sPluginId.'_settings');
			define($this->sConstantId.'_PAGE',		$this->sPluginId.'_page');
			define($this->sConstantId.'_PRFX',		$this->sPluginId.'_field');
			define('T',								constant($sDebugName)?"\t":"");
			define('N',								constant($sDebugName)?"\n":"");
		}
		//
		// getValue
		protected function getValue($s){
			$aForm = $this->getFormdata();
			$o = $aForm[$s];
			$value = $o['value'];
			// if 'values' is set then the value should be an array
			if (isset($o['values'])&&!is_array($value)) $value = array();
			// if the type is 'checkbox' and 'values' is not set then the value should be a boolean
			if (isset($o['type'])&&$o['type']=='checkbox'&&!isset($o['values'])) $value = $value=='on';
			return $value;
		}
		//
		// getObject
		protected function getObject($s){
			$aForm = $this->getFormdata();
			return $aForm[$s];
		}
		//
		// section_text
		public function section_text($for){
			$aForm = $this->getFormdata();
			if (isset($aForm[$for['id']]['text'])) echo '<p>'.$aForm[$for['id']]['text'].'</p>';
		}
		//
		// getFormdata
		protected function getFormdata() {
			if (isset($this->aForm)) return $this->aForm;
			return $this->aForm;
		}
		//
		// setDefaultOptions
		protected function setDefaultOptions($form) {
			foreach ($form as $sId=>$aField) {
				if (!isset($aField['type'])||$aField['type']!='label') {
//				if ($aField['type']!='label') {
					$sDefault = $aField['default'];
					$sVal = get_option($sId);
					if ($sVal===false) update_option($sId, $sDefault);
					$form[$sId]['value'] = $sVal!==false?$sVal:$sDefault;
					$form[$sId]['id'] = $sId;
				}
			}
			return $form;
		}
		//
		// drawFormField
		protected function drawFormField($data) {
			$sId = $data['id'];
			$sLabel = $data['label'];
			$bRequired = isset($data['req'])?$data['req']:false;
			$sRequired = $bRequired?' required="required"':'';
			$sType = isset($data['type'])?$data['type']:'text';
			$sValue = $data['value'];
			$sValTr = ' value="'.$sValue.'"';
			$aValues = isset($data['values'])?$data['values']:array();//$sId=>$sLabel
			$sWidth = isset($data['w'])?' size="'.$data['w'].'" ':'';
			switch ($sType) {
				case 'text': // text
					if (count($aValues)==0) {
						echo '<input name="'.$sId.'" id="'.$sId.'" type="'.$sType.'" '.$sWidth.$sValTr.$sRequired.' size="50" /> ';
					} else {
						foreach ($aValues as $sValueId=>$sValueLabel) {
							$sSubName = $sId.'['.$sValueId.']';
							$sSubId = $sId.$sValueId;
							echo '<label for="'.$sSubId.'">'.$sValueLabel.'</label> <input name="'.$sSubName.'" id="'.$sSubId.'" type="'.$sType.'" value="'.$sValue[$sValueId].'" '.$sWidth.$sRequired.'/> ';
						}
					}
					if (isset($data['text'])) echo '<span class="description">'.$data['text'].'</span>';
				break;
				case 'checkbox': // todo: set checked status if true
					if (count($aValues)==0) {
						echo '<input name="'.$sId.'" id="'.$sId.'" type="'.$sType.'" '.($sValue=='on'?'checked="checked"':'').' '.$sRequired.'/> ';
					} else {
						foreach ($aValues as $sValueId=>$sValueLabel) {
							$sSubName = $sId.'['.$sValueId.']';
							$sSubId = $sId.$sValueId;
							echo '<input name="'.$sSubName.'" id="'.$sSubId.'" type="'.$sType.'" '.((isset($sValue[$sValueId])&&$sValue[$sValueId]=='on')?'checked="checked"':'').' '.$sRequired.'/> <label for="'.$sSubId.'">'.$sValueLabel.'</label> ';
						}
					}
					if (isset($data['text'])) echo '<span class="description">'.$data['text'].'</span>';
				break;
				case 'textarea':
					echo '<textarea name="'.$sId.'" id="'.$sId.'" class="form_'.$sType.'" type="'.$sType.'" '.$sRequired.'>'.$value.'</textarea>';
				break;
				case 'hidden':
					echo '<input name="'.$sId.'" id="'.$sId.'" type="'.$sType.'" value="'.$sValue.'" />';
				break;
				case 'test': // test
					$opt = get_option($sId);
					echo '<input name="'.$sId.'[a]" id="'.$sId.'" type="'.$sType.'"  value="'.$opt['a'].'" '.$sRequired.' />';
					echo '<input name="'.$sId.'[b]" id="'.$sId.'" type="'.$sType.'"  value="'.$opt['b'].'" '.$sRequired.' />';
				break;
				default: echo "<strong>field type '".$sType."' does not exist</strong>";
			}
		}
		//
		// postbox
		protected function postbox($id, $title, $content) {
		?>
			<div id="<?php echo $id; ?>" class="postbox">
				<?php //<div class="handlediv" title="Click to toggle"><br /></div> //useless if state is not stored ?>
				<h3 class="hndle"><span><?php echo $title; ?></span></h3>
				<div class="inside"><?php echo $content; ?></div>
			</div>
		<?php
		}
		//
		// addError
		protected function addError($warning,$message='') {
			$this->aError[] = array($warning,$message);
		}
		//
		// showErrors
		protected function showErrors() {
			foreach ($this->aError as $i=>$error) {
				echo T.T.$this->errorBox($error[0],$error[1]);
			}
		}
		//
		// errorBox
		protected function errorBox($warning,$message) {
			return '<div class="sfb-debug error settings-error"><p><strong>'.$warning.'</strong> '.$message.'</p></div>';
		}
		//
		// like plugin?
		protected function plugin_like($name,$uri='') {
			if ($uri=='') $uri = $this->sPluginHomeUri;
			$this->postbox(
				 'donate'
				,'<strong class="red">If you like '.$name.':</strong>'
				//,'<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_donations"><input type="hidden" name="business" value="FFDDQVHENGNXG"><input type="hidden" name="lc" value="NL"><input type="hidden" name="item_name" value="sfbrowser"><input type="hidden" name="currency_code" value="EUR"><input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHosted"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal, de veilige en complete manier van online betalen."><img alt="" border="0" src="https://www.paypal.com/nl_NL/i/scr/pixel.gif" width="1" height="1"></form>'
				,'<p><a href="'.$uri.'" target="flattr"><img src="'.$this->sPluginRootUri.'flattr-badge-large.png" /></a></p>'
			);
		}
		//
		// plugin_action_links
		public function plugin_action_links($links, $file){ // copied from qtranslate who copied from Sociable Plugin
			//Static so we don't call plugin_basename on every plugin row.
			static $this_plugin;
			if (!$this_plugin) $this_plugin = plugin_basename(dirname(__FILE__).'/wp_'.$this->sPluginId.'.php');
			if ($file == $this_plugin){
				$settings_link = '<a href="options-general.php?page='.$this->sPluginId.'">' . __('Settings', 'sfbrowser') . '</a>';
				array_unshift( $links, $settings_link ); // before other links
			}
			return $links;
		}
	}
}

if (!function_exists("dump")) {
	function dump($s) {
		echo "<pre>";
		print_r($s);
		echo "</pre>";
	}
}

if (!function_exists("trace")) {
	function trace($s) {
		if (SFB_DEBUG) {
			$oFile = @fopen("log.txt", "a");
			$sDump  = $s."\n";
			@fputs ($oFile, $sDump );
			@fclose($oFile);
		}
	}
}