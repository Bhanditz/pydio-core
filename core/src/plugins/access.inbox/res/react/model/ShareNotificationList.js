(function(global) {

    /**************************************************
    /* ShareNotificationList Object
    /*
    /* Handling the display of Notification Collection
    /**************************************************/
    class ShareNotificationList extends Observable {

        // Init
        constructor(pydio, options){
            super();
            this._data = {shares: []};
            this._pydio = pydio;
            this.options = options || {};

            this.load();
            this._pydio.observe("registry_part_loaded", function() {this.load()}.bind(this));
        }

        // Getters / Setters
        getShares(){
            if(!this._data["shares"]) return [];
            return this._data["shares"];
        }

        getSharesByStatus(status) {
            var currentShares = this.getShares(),
                shares = [],
                share;

            for (var i in currentShares) {
                share = currentShares[i];
                if (typeof share.getShareStatus === 'function' && (
                    typeof status == 'number' && share.getShareStatus() == status ||
                    typeof status == 'object' && status.indexOf(share.getShareStatus()) > -1
                )) {
                    shares.push(currentShares[i]);
                }
            }

            return shares;
        }

        // Actions
        load(){
            if(ReactDispatcher.ShareNotificationDispatcher.getClient().getStatus() == 'loading') return;
            ReactDispatcher.ShareNotificationDispatcher.getClient().setStatus('loading');

            ShareNotificationList.loadShares(
                this.options,
                function(transport) {
                    if(transport.responseJSON && transport.responseJSON.shares){
                        this._data.shares = [];

                        var shares = transport.responseJSON.shares;


                        for (var i = 0; i < shares.length; i++) {
                            var share = new ReactModel.ShareNotification(shares[i]);
                            this._data.shares.push(share);
                        }

                        ReactDispatcher.ShareNotificationDispatcher.getClient().setStatus('idle');
                    }
                }.bind(this));
        }

        // Static (eq Client)
        static loadShares(defaultOptions, completeCallback=null, errorCallback=null, settings={}){
            var options = Object.assign(
                defaultOptions,
                {
                    get_action  : 'load_shares'
                }
            );

            PydioApi.getClient().request(options, completeCallback, errorCallback, settings);
        }
    }

    // Globals
    var ReactModel = global.ReactModel || {};
    ReactModel['ShareNotificationList'] = ShareNotificationList;
    global.ReactModel = ReactModel;
    global.ReactModelShareNotificationList = ShareNotificationList; // Set for dependencies management

})(window);