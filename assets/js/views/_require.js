/**
 *  Default app JS config requirements
 */
var juf = ['jquery', 'underscore', 'foundation'];

/**
 *  JumpSeat Require Config
 */
requirejs.config({
    baseUrl: AeroStep.baseUrl + '/assets/js',
    paths: {
        //Base
        'jquery': 'third_party/jquery-11',
        'jquery-ui': 'third_party/jquery-ui',
        'underscore': 'third_party/underscore',
        'foundation': 'third_party/foundation/js/foundation.min',
        'aero': 'aero/user/aero' + AeroStep.min,

        //APIs
        'api/apps' : 'api/app.api' + AeroStep.min,
        'api/guides' : 'api/guide.api' + AeroStep.min,
        'api/languages' : 'api/language.api' + AeroStep.min,
        'api/versions' : 'api/version.api' + AeroStep.min,
        'api/trash' : 'api/trash.api' + AeroStep.min,
        'api/pagedata' : 'api/pagedata.api' + AeroStep.min,
        'api/blacklist' : 'api/blacklist.api' + AeroStep.min,
        'api/users' : 'api/user.api' + AeroStep.min,
        'api/pathways' : 'api/pathway.api' + AeroStep.min,
        'api/roles' : 'api/role.api' + AeroStep.min,
        'api/rolemap' : 'api/rolemap.api' + AeroStep.min,
        'api/rolemap-pathway' : 'api/rolemap-pathway.api' + AeroStep.min,
        'api/rolemap-user' : 'api/rolemap-user.api' + AeroStep.min,

        //Utils
        'utils/aero': 'utils/util.common' + AeroStep.min,
        'utils/export' : 'utils/util.export' + AeroStep.min,
        'utils/dropdown': 'utils/util.dropdown' + AeroStep.min,
        'utils/password' : 'third_party/password',
        'utils/tween': 'third_party/gsap/TweenMax.min',
        'utils/placeholder': 'third_party/foundation/js/vendor/placeholder',
        'utils/foundation-dropdown': 'third_party/foundation/js/foundation/foundation.dropdown',

        //Libraries
        'lib/charts' : 'third_party/Chart.min',
        'lib/uploadify' : 'third_party/uploadify/jquery.uploadify.min'

    },
    shim: {
        //Base
        'jquery': {'exports': '$q' },
        'jquery-ui': {'deps': ['jquery']},
        'underscore': {'exports': '_q', 'deps': ['jquery']},
        'foundation': {'deps': ['jquery']},
        'aero': {'deps': juf },

        //APIs
        'api/apps': {'deps': ['aero', 'utils/aero']},
        'api/guides': {'deps': ['aero', 'utils/aero']},
        'api/versions': {'deps': ['aero', 'utils/aero']},
        'api/trash': {'deps': ['aero', 'utils/aero']},
        'api/pagedata': {'deps': ['aero', 'utils/aero']},
        'api/blacklist': {'deps': ['aero', 'utils/aero']},
        'api/users': {'deps': ['aero', 'utils/aero']},
        'api/pathways': {'deps': ['aero', 'utils/aero']},
        'api/roles': {'deps': ['aero', 'utils/aero']},
        'api/rolemap': {'deps': ['api/roles', 'utils/aero']},
        'api/rolemap-pathway': {'deps': ['api/pathways', 'api/rolemap', 'utils/aero']},
        'api/rolemap-user': {'deps': ['api/roles', 'api/rolemap', 'utils/aero']},

        //Utils
        'utils/aero': {'deps': juf.concat('utils/tween') },
        'utils/export': {'deps': juf.concat('utils/aero') },
        'utils/password': {'deps': juf },
        'utils/tween': {'deps': juf },
        'utils/placeholder': {'deps': juf },
        'utils/foundation-dropdown': {'deps': juf },
        'utils/dropdown': {'deps': juf },

        //Libraries
        'lib/charts' : {'deps': [] },
        'lib/uploadify' : {'deps': juf }
    }
});
