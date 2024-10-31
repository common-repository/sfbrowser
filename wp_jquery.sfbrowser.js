;(function($) {
	// private variables
	var oSettings = { debug: true };
	var ss = oSettings;
	var oSfb = {
		 select:	function(aFiles){insertSFBContent(aFiles,"a")}
		,debug:		false
		,swfupload:	false
		,dirs:		true
		,plugins:	["imageresize"]
		,w:			700
		,h:			500
		,bgcolor:	"#F9F9F9"
		,bgalpha:	.7
	}
	var oData = {
		 image: {
			 folder:"img/"
			,allow:['jpg','jpeg','gif','png']
			,select:function(aFiles){insertSFBContent(aFiles,"img")}
			,bgcolor: "#F9F9F9"
			,resize:	null
		 },video: {
			 folder:"vid/"
		 },audio: {
			 folder:"aud/"
		 },media: {
			 folder:""
		 }
	};
	// default settings
	$.wpadminsfb = ww = {
		 id: "wpadminsfb"
		,defaults: {
			 debug:		false
			,version: "1.2.3"
			,siteUri:	''
			,filePrefixUri:	''
			,override:	{}
			,relativePath: false
			,insertionRules: '{}'
			,sfbObject: {}
		}
		,open: function(type) {					$.sfb($.extend({},oSfb,oData[type])); }
		,addInsertionRule: function(el) {		addInsertionRule(el); }
		,remInsertionRule: function(el,id) {	remInsertionRule(el,id); }
		,buildInsertionRule: function(id){		buildInsertionRule(id); }
	};
	// call
	$.fn.extend({
		wpadminsfb: function(_settings) {
			$.extend(oSettings, ww.defaults, _settings);
			trace($.wpadminsfb.id+" "+ss.version,true);
			//
			// relativePath?
			var aBack = ss.filePrefixUri.split('/');
			var iBack = Math.max(0,aBack.length-2);
			var sBack = '';
			for (var i=0;i<iBack;i++) sBack += '../';
			ss.prefix = ss.relativePath?sBack:ss.siteUri+"/";
			//
			$.extend(oSfb,ss.sfbObject);
			//
			if (ss.resize) oData.image.resize = ss.resize;
			oData.image.folder = ss.imageFolder+"/";
			oData.video.folder = ss.videoFolder+"/";
			oData.audio.folder = ss.audioFolder+"/";
			oData.media.folder = ss.mediaFolder+"/";
			//
			// overrides
			if (ss.override.media) {
				// media should have been removed by php but just in case...
				$("li#menu-media").remove();
				// set SFBrowser menu item
				var $Li = $("#toplevel_page_sfbrowser_media");
				$Li.find(".wp-menu-image img").remove();
				$Li.find(">a").removeAttr('href').click(function(e){$.sfb(oSfb);});
			}
			//
			// featured image
			if (ss.override.feature) {
				var fnSet = function fnSet($A){
					$A.removeAttr("onclick").removeAttr("href").css({textDecoration:'underline'}).removeClass("thickbox").click(function(e){
						var oFeature = $.extend({}, oSfb, oData.image);
						oFeature.select = function(aFiles){
							var oFile = aFiles[0];
							if (oFile.wpPostsId) {
								WPSetAsThumbnail(oFile.wpPostsId, ss.featurenonce);
							} else {
								alert('A file has to be present in the Worpress media library. Click the Wordpress icon in the file browser to do so.');
								return false;
							}
						};
						$.sfb(oFeature);
					});
				}
				//
				$FiA = $("a#set-post-thumbnail");
				fnSet($FiA);
				$FiA.parents("div.inside").contentChange(function(e){
					fnSet($(this).find("#set-post-thumbnail"));
				});
			}
			//
			$(window).load(function () { // todo: test
				replaceSrc();
				//if(typeof tinyMCE == "undefined") return;
				//if (!tinyMCE) return;
				try {
					tinyMCE&&tinyMCE.activeEditor&&tinyMCE.activeEditor.onChange.add(replaceSrc);
					$("#edButtonPreview").click(replaceSrc);
				} catch (err) {
					trace("tinyMCE not found",true); // TRACE ### tinyMCE
				}
			});
			//
			// admin form options
			$.each(["image","video","audio","media"],function(i,s){
				var $Input = $("#sfbrowser_overrideTinyMCE"+i);
				if (!$Input.is(":checked")) $("#sfbrowser_"+s+"Directory").parents("tr:first").hide();
				$Input.change(function(){
					var $Tr = $("#sfbrowser_"+s+"Directory").parents("tr:first");
					if (!$(this).is(":checked")) $Tr.hide('fast');
					else $Tr.show('fast');
					//// check all
					//var iNumChecked = 0;
					//for (var j=0;j<4;j++) iNumChecked += $("#sfbrowser_overrideTinyMCE"+j).is(":checked")?1:0;
					//if (iNumChecked===0){}
				});
			});
			//
			// if feature image is set, wp-db plugin must be on
			var $Feature = $("#sfbrowser_featureImage");
			var $Wpdb = $("#sfbrowser_plugins3");
			$Feature.change(function(e){
				if ($(this).is(":checked")) $Wpdb.attr("checked","checked");
			});
			$Wpdb.change(function(e){
				if ($(this).not(":checked")) $Feature.removeAttr("checked");
			});
		}
	});
	// insertSFBContent
	function insertSFBContent(aFiles,sType) {
		//aFiles[0] = {
		//file:	../wp-content//uploads/img/4304a005c49d1dc4c43dd20a8435d09c_l.jpg
		//mime:	jpg
		//rsize:	127509
		//size:	125kB
		//time:	1283419744
		//date:	2-9-2010 11:29
		//width:	356
		//height:	500
		//
		var sMime = aFiles[0].mime;
		//
		var sHTML = '';
		$.each(ss.insertionRules,function(s,o){
			if (o.e.indexOf(sMime)!==-1) {
				if (aFiles.length>1) {
					var aMlt = o.m.split('|');
					sHTML = aMlt[0];
					var sItem = aMlt[0];
					if (aMlt.length>1) {
						sHTML = aMlt[0];
						sItem = aMlt[1];
					}
					if (sItem=='') sItem = o.s;
					$.each(aFiles,function(i,oFile){
						var sFile = makeFilePath(oFile);
						var sName = sFile.split("/").pop().replace('.'+oFile.mime,'');
						var sAdd = sItem.replace('%file',ss.prefix+sFile).replace('%name',sName);
						$.each(oFile,function(ss,v){
							if (ss!='file') sAdd = sAdd.replace('%'+ss,v);
						});
						sHTML += sAdd+"\n";
					});
					if (aMlt.length>2) sHTML += aMlt[0];
					sHTML += "\n";
				} else {
					var oFile = aFiles[0];
					var sFile = makeFilePath(oFile);
					var sName = sFile.split("/").pop().replace('.'+oFile.mime,'');
					sHTML = o.s.replace('%file',ss.prefix+sFile).replace('%name',sName);
					$.each(oFile,function(ss,v){
						if (ss!='file') sHTML = sHTML.replace('%'+ss,v);
					});
					sHTML += "\n";
				}
			}
		});
		// no rules are set, use default
		if (sHTML=="") {
			var sFile = makeFilePath(aFiles[0]);
			sHTML = "<img src=\""+ss.prefix+sFile+"\" />";
			switch (sType) {
				case "a":
					if (aFiles.length>1) {
						sHTML = "<ul>";
						for (var i=0;i<aFiles.length;i++) {
							var sFile = makeFilePath(aFiles[i]);
							sHTML += "<li><a href=\""+ss.prefix+sFile+"\">"+sFile.split("/").pop()+"</a></li>";
						}
						sHTML += "</ul>";
					} else {
						sHTML = "<a href=\""+ss.prefix+sFile+"\">"+sFile.split("/").pop()+"</a>";
					}
				break;
			}
		}
		//
		var $QuickPress = $("#dashboard_quick_press");
		if ($QuickPress.length===0) { // TinyMCE
			try { // bloody editor
				// looks like this is in visual mode
				tinyMCE.activeEditor.selection.setContent(sHTML);
				replaceSrc();
			} catch (e) { // looks like this is in html mode
				try {
					edInsertContent(edCanvas,sHTML);
				} catch (err) {
					trace('tinyMCE seems not present '+err);
				}
			}
		} else { // Quickpress
			insertAtCaret($QuickPress.find("#content").get(0),sHTML);
		}
	}
	// makeFilePath
	function makeFilePath(oFile) {
		return oFile.file.replace("..data/",ss.siteUri+"/data/").replace("../","").replace("//","/");
	}
	// replaceSrc
	function replaceSrc() {
		$("iframe").contents().find("body img").each(function(){
			var sSrc = $(this).attr("src");
			if (sSrc.substr(0,3)!="../") $(this).attr("src","../"+sSrc);
		});
		//$("iframe").contents($("iframe").contents().replace('../wp-content/','wp-content/'))
	}
	//
	// insertAtCaret
	function insertAtCaret(txtarea,text) {
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		}
		else if (br == "ff") strPos = txtarea.selectionStart;
		
		var front = (txtarea.value).substring(0,strPos);  
		var back = (txtarea.value).substring(strPos,txtarea.value.length); 
		txtarea.value=front+text+back;
		strPos = strPos + text.length;
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			range.moveStart ('character', strPos);
			range.moveEnd ('character', 0);
			range.select();
		}
		else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		txtarea.scrollTop = scrollPos;
	}
	//
	// insertion rules
	function addInsertionRule(el) {
		$($("#insertionAdd").val()).insertBefore($(el));
	}
	function remInsertionRule(el,id) {
		$(el).parents("p:first").remove();
		buildInsertionRule(id);
	}
	function buildInsertionRule(id) {
		var $Input = $('#'+id);
		var $PRules = $Input.parent().find('.insertionRule');
		var o = {};
		$PRules.each(function(i,el){
			var $P = $(el);
			var sExt = $P.find('.sfbir_e').val();
			var sSng = $P.find('.sfbir_s').val();
			var sMlt = $P.find('.sfbir_m').val();
			if (!(sExt==''||sSng=='')) {
				o['rule_'+i] = {
					 'e':sExt
					,'s':sSng
					,'m':sMlt
				};
			}
		});
		//trace(JSON.stringify(o));
		$Input.val(JSON.stringify(o));
	}
	// ajax error callback
	function onError(req, status, err) {	trace("sfb ajax error "+req+" "+status+" "+err); }
	//
	// trace
	function trace(o,v) {
		if ((v||ss.debug)&&window.console&&window.console.log) {
			if (typeof(o)=="string")	window.console.log(o);
			else						for (var prop in o) window.console.log(prop+":\t"+String(o[prop]).split("\n")[0]);
		}
	}
})(jQuery);



;(function($) {
	$(function() {
		setInterval(function(){
			if(window.watchContentChange){
				for( i in window.watchContentChange){
					if(window.watchContentChange[i].element&&window.watchContentChange[i].element.data("lastContents") != window.watchContentChange[i].element.html()){
						window.watchContentChange[i].callback.apply(window.watchContentChange[i].element);
						window.watchContentChange[i].element.data("lastContents", window.watchContentChange[i].element.html())
					};
				}
			}
		},500);
	});
	$.fn.extend({
		contentChange: function(callback) {
			var elms = $(this);
			elms.each(function(i){
				var elm = jQuery(this);
				elm.data("lastContents", elm.html());
				window.watchContentChange = window.watchContentChange ? window.watchContentChange : [];
				window.watchContentChange.push({"element": elm, "callback": callback});
			});
			return elms;
		}
	});
})(jQuery);