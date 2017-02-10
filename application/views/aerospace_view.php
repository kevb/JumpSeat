<? $debug = (ENVIRONMENT == "development") ? true : false; ?>

"use strict";

//Local Storage Cross Domain
window.XdUtils=window.XdUtils||function(){function a(a,b){var c,d=b||{};for(c in a)a.hasOwnProperty(c)&&(d[c]=a[c]);return d}return{extend:a}}(),window.xdLocalStorage=window.xdLocalStorage||function(){function a(a){j[a.id]&&(j[a.id](a),delete j[a.id])}function b(b){var c;try{c=JSON.parse(b.data)}catch(d){}c&&c.namespace===g&&("iframe-ready"===c.id?(l=!0,h.initCallback()):a(c))}function c(a,b,c,d){i++,j[i]=d;var e={namespace:g,id:i,action:a,key:b,value:c};f.contentWindow.postMessage(JSON.stringify(e),"*")}function d(a){h=XdUtils.extend(a,h);var c=document.createElement("div");window.addEventListener?window.addEventListener("message",b,!1):window.attachEvent("onmessage",b),c.innerHTML='<iframe id="'+h.iframeId+'" src='+h.iframeUrl+' style="display: none;"></iframe>',document.body.appendChild(c),f=document.getElementById(h.iframeId)}function e(){return k?l?!0:(console.log("You must wait for iframe ready message before using the api."),!1):(console.log("You must call xdLocalStorage.init() before using it."),!1)}var f,g="cross-domain-local-message",h={iframeId:"cross-domain-iframe",iframeUrl:void 0,initCallback:function(){}},i=-1,j={},k=!1,l=!0;return{init:function(a){if(!a.iframeUrl)throw"You must specify iframeUrl";return k?void console.log("xdLocalStorage was already initialized!"):(k=!0,void("complete"===document.readyState?d(a):window.onload=function(){d(a)}))},setItem:function(a,b,d){e()&&c("set",a,b,d)},getItem:function(a,b){e()&&c("get",a,null,b)},removeItem:function(a,b){e()&&c("remove",a,null,b)},key:function(a,b){e()&&c("key",a,null,b)},clear:function(a){e()&&c("clear",null,null,a)},wasInit:function(){return k}}}();

if(!AeroStep){

    /**
    *   Aero Storage Cross Domain
    */
    var aeroStorage = {

        override : true,

        /**
        *  Get local storage item
        */
        getItem : function(key, callback, cross){

			if(cross && this.override){
                xdLocalStorage.getItem(key, function(d){ callback(d.value); });
                return true;
            }else if(callback){
                callback(localStorage.getItem(key));
            }else{
                return localStorage.getItem(key);
            }
        },

        /*
        *  Set local storage item
        */
        setItem : function(key, value, callback, cross){

            if(cross && this.override){
                xdLocalStorage.setItem(key, value, function(d){ callback(d)});
            }else {
                localStorage.setItem(key, value);

                if(callback) callback(value);
            }
        },

        /*
        *  Set local storage item
        */
        removeItem : function(key, cross){

			var session = ['audit', 'pause', 'forward', 'forwardUrl', 'cds', 'current', 'index', '404', 'end'];

            if(key == "all"){
                // Clear All
                if(this.override){
                    xdLocalStorage.clear(function (data) { /* callback */ });
                }

				for(var i in session){
					localStorage.removeItem("aero:session:" + session[i]);
				}
            }else {
                if(cross && this.override){
                    xdLocalStorage.removeItem(key, function (data) {});
                }else {
                    localStorage.removeItem(key);
                }
            }
        }
    };

	/**
	*	Configuration
	*/
	var AeroStep = {
		cache : <?= $cache; ?>,
		lang : <?= $lang; ?>,
		debug : <?= $debug ? "true":"false" ?>,
		admin : <?= $admin ? "true" : "false" ?>,
		baseUrl : "<?= base_url(); ?>",
		host : "<?= $app; ?>",
		license : "<?= $_SESSION['license'] ?>",
		locale : "<?= substr(Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']),0,2); ?>",
		rootLocale : "<?= $rootLocale; ?>",

		<? if ($require != ""){ ?>
			required : {
				ready : function(){
					return <?= $require; ?>;
				}
			},
		<? } ?>

		/**
		*	 Get the username
		*/
		getUsername : (function(){

			var user = "<?= $username; ?>";
			if(user == ""){
              	user = "guest@jumpseat.io";

				//Use metadata?
				if(AeroStep.data.username) user = AeroStep.data.username();
			}

			//Has session?
			var ls = aeroStorage.getItem('aero:username');

			if(ls !== user){
		 		aeroStorage.setItem('aero:cache', new Date().getTime());

				//Clear aero
				AeroStep.session.destroy('aero:');
			}

			//Save current user
            aeroStorage.setItem('aero:username', user);

			return user;
		}),


        /**
        *   Get URL Parameter Value
        */
        getUrlParam : function(name){

            name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
            return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));

        },

		/**
		*   Get the current URL with replacement
		*/
		getSubURL : function(url){
			if(!url) url = window.location.href;

			try {
				var urls = AeroStep.data;

				if(urls){
					for(var i in urls){
                        for(var j in urls[i]){
                            if(urls[i][j]['regex']){
                                var reg = new RegExp(urls[i][j]['regex'].replace(/\//g, '\/'), "i");
								var val = (eval(urls[i][j]['value']));
								if(val && val != "") url = url.replace(reg, val);
                            }
                        }
					}
				}
			}catch(err){
				Aero.log('Problem with URL Sub: ' + err, 'error');
			}

			return url;
		},

		config : {
			"baseUrl" : "<?= base_url(); ?>",
			"paths": {
				"jquery": "assets/js/third_party/jquery",
				"underscore": "assets/js/third_party/underscore",
				"aero" : "assets/js/aero/user/aero",
                "aero-main" : "assets/js/aero/user/_main",
                "aero-guide" : "assets/js/aero/user/aero-guide",
				"aero-pathway" : "assets/js/aero/user/aero-pathway",
                "aero-quiz" : "assets/js/aero/user/aero-quiz",
				"aero-media" : "assets/js/aero/user/aero-media",
				"aero-step" : "assets/js/aero/user/aero-step",
				"aero-tip" : "assets/js/aero/user/aero-tip",
				"aero-audit" : "assets/js/aero/user/aero-audit"
				<? if($admin){ ?>
                    ,"aero-admin-main" : "assets/js/aero/admin/_main"
                    ,"aero-admin" : "assets/js/aero/admin/aero-admin"
                    ,"aero-admin-guide" : "assets/js/aero/admin/aero-guide"
                    ,"aero-admin-step" : "assets/js/aero/admin/aero-step"
                    ,"aero-admin-pathway" : "assets/js/aero/admin/aero-pathway"
                    ,"aero-admin-role" : "assets/js/aero/admin/aero-role"
                    ,"aero-admin-picker" : "assets/js/aero/admin/aero-picker"
                    ,"aero-admin-quiz" : "assets/js/aero/admin/aero-quiz"
                    ,"aero-editor" : "assets/js/third_party/editor2/aero-editor"
				<? } ?>
				<? if($debug){ ?>,"aero-test" : "assets/js/_test/services"<? } ?>
			},
			"shim": {
                "jquery": {				"exports": "$q" }
                ,"underscore": {		"exports": "_q" }
                ,"aero" : {			 	"deps" : ["jquery", "underscore"] }
                ,"aero-main" : {     	"deps" : ["aero", "aero-tip", "aero-pathway", "aero-guide"] }
                ,"aero-guide" : {		"deps" : ["aero", "aero-tip", "aero-pathway"] }
                ,"aero-pathway" : { 	"deps" : ["aero"] }
                ,"aero-quiz" : { 	    "deps" : ["aero"] }
				,"aero-media" : { 	    "deps" : ["aero"] }
                ,"aero-step" : {    	"deps" : ["aero"] }
                ,"aero-audit" : {    	"deps" : ["aero"] }
                ,"aero-tip": { 			"deps" : ["aero", "aero-step", "aero-audit"] }
            <? if($debug){ ?>
                ,"aero-test": { "deps": ["aero"] }
            <? } ?>
            <? if($admin){ ?>
                ,"aero-admin" : { 		"deps": ["aero"] }
                ,"aero-admin-guide" : { "deps": ["aero", "aero-guide", "aero-admin"] }
                ,"aero-admin-step" : {  "deps": ["aero", "aero-step", "aero-admin"] }
                ,"aero-admin-pathway" :{"deps": ["aero", "aero-admin"] }
                ,"aero-admin-role" : { 	"deps": ["aero", "aero-admin"] }
                ,"aero-admin-picker" : {"deps": ["aero", "aero-admin"] }
                ,"aero-admin-quiz" : { 	"deps": ["aero", "aero-admin", "aero-admin-guide", "aero-admin-step"] }
                ,"aero-admin-main": { 	"deps": ["aero", "aero-step", "aero-guide", "aero-admin-guide","aero-admin-step","aero-admin-pathway","aero-admin-role","aero-admin-picker","aero-admin-quiz"] }
                ,"aero-editor": { 		"deps": ["jquery"] }
            <? } ?>
			},
			"lib_list" : [
				"jquery", "underscore", "aero", "aero-main", "aero-guide", "aero-pathway", "aero-step", "aero-tip", "aero-audit", "aero-quiz", "aero-media"
                <? if($debug){ ?>,"aero-test"<? } ?>
                <? if($admin){ ?>,"aero-admin","aero-admin-guide","aero-admin-step","aero-admin-pathway","aero-admin-role","aero-admin-picker","aero-admin-quiz","aero-admin-main","aero-editor"<? } ?>
			],
			"css": [
				"assets/css/aero.css",
                <? if($admin){ ?>"assets/js/third_party/editor2/ui/trumbowyg.min.css",<? } ?>
				"assets/css/font-awesome.min.css"
			]
		},

		require : function(callback){

			var s = document.createElement('script');
				s.src = this.baseUrl + "assets/js/third_party/require.js";
				s.async = true;
				s.onreadystatechange = s.onload = function() {
					var state = s.readyState;
					if (!callback.done && (!state || /loaded|complete/.test(state))) {
						callback.done = true;
						callback();
					}
			};

			document.getElementsByTagName('body')[0].appendChild(s);
		},

		loadCss : function(){
			var css_list = this.config.css;

			for (var i = 0; i < css_list.length; i++) {
				var link = document.createElement("link");
				link.type = "text/css";
				link.rel = "stylesheet";

				link.href = this.baseUrl + css_list[i];
				if(css_list[i].indexOf("//") >= 0){
					link.href = css_list[i];
				}

				var ext = css_list[i].substr(css_list[i].length - 4);
				if (ext != ".css") link.href += ".css";
				document.getElementsByTagName("head")[0].appendChild(link);
			}
		}
	};

	try {
		AeroStep.data = {
			<?= $pagedata; ?>
		}
	}catch(err){
		console.log('%c Pagedata is broken: '+err, 'background: red; color: #fff');
	}

    try {
       <?= (isset($fire)) ? $fire : ""; ?>
    }catch(err){
        console.log('%c Fire is broken: '+err, 'background: red; color: #fff');
    }

	AeroStep.session = {
		destroy : function(key){

			if(Aero && Aero.audit && Aero.audit.enabled) Aero.audit.save();

			//Clear session
            aeroStorage.removeItem("all");
		 }
	};

	AeroStep.init = function(callback, require){
	    var timer = null;
		if(!require){ callback(); return; }

		function wait(){
	        timer = window.setInterval(function(){
	            try {
	                if(require.ready()){
						if(AeroStep.debug) console.log("Found required metadata");
						callback();

	                    clearInterval(timer);
	                }else{
						if(AeroStep.debug) console.log("Observing for required...");
					}
	            }catch(err){
	                if(AeroStep.debug) console.log("Observing for required...");
	            }
	        }, 500);
		}
	    wait();
	};

	AeroStep.ready = function(callback, required){
	     new AeroStep.init(callback, required);
	};

	//Make sure window is big enough
	if(window.innerWidth > 500) {
		//Load on ready
		AeroStep.ready(function(){
			AeroStep.require(function(){
				AeroStep.loadCss();
				xdLocalStorage.init({ iframeUrl: "<?= base_url(); ?>assets/tpl/crossdomain.html", initCallback: function (){

					//Storage ready
					aerorequirejs.config(AeroStep.config);
					aerorequirejs(AeroStep.config.lib_list);
				}});
			});
		}, AeroStep.required);
	}
}
