mlpn = {

    // Durée du cookie pour l'alert en home (en jours)
    alertCookieDuration: 7,

    // Initialisation du site
    init: function()
    {

        // Initialisation des éléments Boostrap tooltip
        this.initBootstrapTooltip();
    },

    /**
     * Initialisation des éléments Bootstrap Tooltip
     */
    initBootstrapTooltip: function()
    {
        $('[data-toggle="tooltip"]').tooltip();
    },

    /**
     * Initialisation de l'alerte modal
     */
    initBootstrapModalAlert: function()
    {
        modalAlert = $('#modalAlert');
        modalAlert.modal('show');

        modalAlert.on('hidden.bs.modal', function (e) {
            var date = new Date();
            date.setDate(date.getDate() + 7);
            document.cookie = "hideAlert=true;expires=" + date.toUTCString();
            console.log(date.toUTCString());
        })
    }
};
