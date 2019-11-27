mlpn = {

    // Initialisation du site
    init: function(){

        // Initialisation des éléments Boostrap tooltip
        this.initBootstrapTooltip();
    },

    /**
     * Initialisation des éléments Bootstrap Tooltip
     */
    initBootstrapTooltip: function() {
        $('[data-toggle="tooltip"]').tooltip();
    }
};
