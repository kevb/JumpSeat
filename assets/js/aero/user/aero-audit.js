"use strict";

/**
 * Audit Object
 * @author Trevor Dell
 * @type {{key: string, defaults: Function, get: Function, update: Function, getTime: Function, padZero: Function}}
 */
Aero.model.audit = {

	/**
	 *  Services URL
	 */
	key: 'aero:session:audit',

	/**
	 *  Default model
	 */
	defaults: function() {

		return {
			"user": Aero.constants.USERNAME,
			"guideid": Aero.tip._guide.id,
			"pathwayid": Aero.pathway.pathwayid,
			"progress": 1,
			"perc" : Math.round((1 / Aero.tip._guide.step.length) * 100),
			"timeStart": "",
			"timeStamp": this.getTime(),
			"timeTotal": ""
		}
	},

	/**
	 * Retrieve local storage
	 */
	get: function() {
        return JSON.parse(aeroStorage.getItem(this.key));
	},

	/**
	 * Update timestamp and write local storage
	 */
	update: function(data) {
        if(AeroStep.admin || !Aero.tip._guide) return;

        var old = JSON.parse(aeroStorage.getItem(this.key));

        if(old && old.id != "" && data.id == "") data.id = old.id;

		// This function is called on beforeShow step
		// Update local storage with new step activity
		data.timeStamp = this.getTime();

		aeroStorage.setItem(this.key, JSON.stringify(data));
	},

	/**
	 * Get time and date in YYYY-MM-DD HH:MM:SS format
	 */
	getTime: function() {
		var date = new Date();
		return  date.getFullYear() + '-'
		            + this.padZero(date.getMonth()+1) + '-'
					+ this.padZero(date.getDate()) +  ' '
		            + this.padZero(date.getHours()) + ':'
		            + this.padZero(date.getMinutes()) + ':'
		            + this.padZero(date.getSeconds());
	},

	/**
	 * Make months, minutes, etc have a padded zero
	 */
	padZero: function(i) {
		return ('0' + i).slice(-2);
	}
};

/**
 *  View definition -
 */
Aero.view.audit = {

	timeStart: 0,
	timeTotal: 0,

	/**
	 *  @function setEvents for audit
	 */
	setEvents: function(){

		var self = this;
			self.startTimer();

		//On page unload or end guide save audit
		$q(window).on('beforeunload', function(){
	         Aero.audit.save();
	    });

		//User tab focusing
		$q(window)
			.on('focus.aerof', function() {
				Aero.view.audit.startTimer();
			})
			.on('blur.aerob', function() {
				Aero.view.audit.stopTimer();
			});
	},

	removeEvents: function() {
		$q(window).off('blur.aerob focus.aerof');
	},

	startTimer: function() {
		this.timeStart = new Date().getTime();
	},

	stopTimer: function() {
		var currentTime = new Date().getTime();
		this.timeTotal = (currentTime - this.timeStart) + this.timeTotal;
	}
};

/**
 *
 */
Aero.audit = {

	enabled: true,
	url: 'api/audit',
	id: '',
	furthestStep: 0,

	/**
	 * @function Initialize the audit
	 */
	init: function(callback) {

        var isNew = true;

		//Reset for non page load
		Aero.audit.furthestStep = 0;
		Aero.view.audit.timeTotal = 0;

        if(AeroStep.admin ||
		    !Aero.constants.USERNAME || typeof Aero.tip._guide === 'undefined' || !Aero.tip._guide ||
            aeroStorage.getItem('aero:session:audit')) isNew = false;

        Aero.view.audit.setEvents();

        if(isNew) {
            this.create(callback);
        }else{
            callback();
        }
	},

	/**
	 * @function Create new audit log
	 */
	create: function(callback) {
        if(AeroStep.admin) return;

		// This function is called only from init for new sessions not to be called if
		// Create new audit log (row) in database
		// Return id
		// Create aeroStorage session with new id and defaults
		var self = this;
		var data = Aero.model.audit.defaults();
			data.timeStart = data.timeStamp;
			data.timeTotal = Aero.view.audit.timeTotal;

        Aero.send(this.url + '/create', { data : JSON.stringify(data) }, function (r) {

            self.id = r;
            data.id = r;

            Aero.model.audit.update(data);
            if(callback) callback();
        }, 'GET');
	},

    /**
     * @function Save audit info to server
     */
	update: function() {
        if(AeroStep.admin) return;

		var data = Aero.model.audit.get();
		var current = Aero.tip._current;

		// If user has been at this step before, don't log
		if (current < this.furthestStep) {
			return;
		} else {
			this.furthestStep = current;
		}

		data.progress = (current + 1);
		data.perc = Math.round(((current + 1) / Aero.tip._guide.step.length) * 100);
		data.id = this.id;
		data.timeTotal = Aero.view.audit.timeTotal;

		Aero.model.audit.update(data);
	},

	/**
	 * @function Save audit info to server
	 * @param {function} callback function
	 */
	save: function(callback) {

        if(AeroStep.admin || !Aero.tip._guide) return;

		var data = Aero.model.audit.get();

		if(data){
			Aero.view.audit.stopTimer();
			data.timeTotal = Aero.view.audit.timeTotal;

			Aero.view.audit.removeEvents();

			// Save local storage to server using the id
			Aero.send(this.url + '/update', { data : JSON.stringify(data) }, function(r) {
				Aero.model.audit.update(data);
			}, 'GET');
		}
		if (callback) callback();
	}
};
